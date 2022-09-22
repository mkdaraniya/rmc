define([
    'jquery',
    'mage/template',
    'text!Swissup_CheckoutSuccess/template/layout-2.0.html',
    'Magento_Ui/js/modal/modal' // 2.3.3: create 'jquery-ui-modules/widget' dependency
], function ($, mageTmpl, tmplLayout) {
    'use strict';

    /**
     * Listen click on inherit checkbox to enable/disbale layout.
     */
    $('body').on('change', '.config-inherit', (event) => {
        const $layout = $(event.currentTarget)
            .closest('.use-default')
            .siblings('.value')
            .find('.success-page-layout');

        $layout.trigger('swissup::toggleIsInherit');
    });

    $.widget('swissup.checkoutSuccessLayout2', {
        /** @inheritdoc */
        _create: function() {
            const me = this;
            const $el = me.element;
            const loadIframe = (event, builder) => {
                event.preventDefault();
                $el.find('[data-role="spinner"]').show();
                $el.find('iframe').attr('src', me.getIframeSrc({
                    builder: builder,
                    previewObjectId: $el.find('.order-wrapper input').val()
                }));
            }

            me.isInherit = me.options.isInherit;
            me._on({
                'click .button-full-scr': (event) => {
                    event.preventDefault();
                    $('body').toggleClass('success-page-layout--full');
                },
                'click .button-view': (event) => {
                    loadIframe(event, 0);
                },
                'click .button-builder': (event) => {
                    const $toolbar = $el.find('.builder-toolbar');

                    loadIframe(event, 1);
                    $toolbar.find('.actions-view').hide();
                    $toolbar.find('.actions-builder').show();
                },
                'click .button-blocks': (event) => {
                    event.preventDefault();
                    me.toggleBuilderBlocks();
                },
                'swissup::iframeLoaded': (event) => {
                    const iframeSrc = $el.find('iframe').attr('src') || '';

                    if (iframeSrc.indexOf('builder=1') !== -1) {
                        // Iframe loaded with builder enabled
                        me._initBuilder();
                    }

                    $el.find('[data-role="spinner"]').hide();
                },
                'swissup::toggleIsInherit': (event) => {
                    me.isInherit = !me.isInherit;
                    me.$layout.remove();
                    me.$layout = me._renderLayout();
                },
                'modalclosed .success-page-settings': (event, modal) =>  {}
            });

            $el.find('textarea').hide();
            me.$layout = me._renderLayout();
            $el.find('[data-role="spinner"]').hide();

        },

        /**
         * @private
         */
        _renderLayout: function () {
            const $layout = $(mageTmpl(tmplLayout, this));

            $layout.appendTo(this.element);
            $layout.trigger('contentUpdated');

            return $layout;
        },

        /**
         * @private
         */
        _initBuilder: function () {
            const me = this;
            const $builder = this.getBuilderDocument();

            if (!$builder) return;

            // Initialize sortable (conatiners with success page blocks).
            $builder.find('.checkout-success-container').sortable({
                connectWith: $builder.find('.checkout-success-container'),
                start: (event, ui) => {
                    // Make placeholder look the same as grabbed element
                    ui.placeholder.html(ui.item.data('empty') ? '' : ui.item.html());
                },
                // tolerance: "pointer",
                update: () => {
                    me.updateContainers();
                }
            });

            // Initialize draggable (side bar with allowed blocks).
            $builder.find('.allowed-blocks [data-type="block"]').draggable({
              connectToSortable: $builder.find('.checkout-success-container'),
              helper: "clone",
              revert: "invalid", // when not dropped, the item will revert back to its initial position
              start: (event, ui) => {
                const $orig = $(event.target);

                // Set width and height for grabbed element.
                ui.helper.height($orig.height());
                ui.helper.width($orig.width());

                // Hide allowed blocks sidebar.
                me.toggleBuilderBlocks();
              },
              stop: (event, ui) => {
                const blockName = ui.helper.data('name');

                // Reset width and height of grabbed element.
                // So when it dropped styles aaplied to it.
                ui.helper.height('');
                ui.helper.width('');

                // Send request to get inserted block content
                me.fetchAndReplace(ui.helper);
              }
            });

            // Listen for click on delete block button.
            $builder.on('click', '[data-type="block"] .button-delete', (event) => {
                const $block = $(event.target).closest('[data-type="block"]');

                event.preventDefault()
                $block.hide(300, () => {
                    $block.remove();
                    me.updateContainers();
                })
            });

            // Listen click on get config button
            $builder.on('click', '[data-type="block"] .button-settings', (event) => {
                const $block = $(event.target).closest('[data-type="block"]');

                event.preventDefault();
                me.fetchSettings($block).done((data, textStatus, jqXHR) => {
                    const $settings = $('.success-page-settings');

                    $settings.html(data).trigger('openModal');
                    // set config values to form
                    $.each($block.data('config'), (key, value) => {
                        $settings.find(`[name="${key}"]`).val(value);
                    });
                    $settings.one('modalclosed', (event, modal) => {
                        var settings = {};

                        $settings.find(':input').serializeArray().each((item) => {
                            settings[item.name] = item.value
                        });

                        $block.data('config', settings);
                        $block.attr('data-config', JSON.stringify(settings));
                        me.updateContainers();
                        me.fetchAndReplace($block);
                    });
                })
            });
        },

        getIframeSrc: function(params) {
            const $el = this.element;
            var src = decodeURI(this.options.iframeSrc);

            $.each(params, (name, value) => {
                src += ((src.indexOf('?') === -1) ? '?' : '&') + `${name}=${value}`;
            });

            return encodeURI(src);
        },

        updateContainers: function () {
            const $el = this.element;
            const $builder = this.getBuilderDocument();

            var containers = [];
            $builder.find('[data-type="container"]').each(function () {
                var children = [];

                $(this).find('[data-type="block"]').each((i, b) => {
                    children.push({
                        name: $(b).data('name'),
                        config: $(b).data('config')
                    });
                });

                containers.push({
                    name: $(this).data('name'),
                    children: children
                });
            });

            $el.find('textarea').val(JSON.stringify(containers));
        },

        fetchAndReplace: function ($el) {
            const me = this;
            const $builder = me.getBuilderDocument();
            const name = $el.data('name');

            const success = (data, textStatus, jqXHR) => {
                const $block = $(data).find(`[data-name="${name}"]`).first();
                const iframeJquery = me.element.find('iframe').get(0)?.contentWindow?.jQuery;

                $el.replaceWith($block);
                // Trigger mage-init execution.
                iframeJquery($block.get(0)).trigger('contentUpdated');
                // apply ko binding
                iframeJquery($block.get(0)).children().applyBindings();
            };

            var requestData = {};

            requestData[$el.data('type')] = name;
            requestData['config'] = $el.data('config');

            // \Magento\Checkout\Controller\Onepage\Success implements HttpGetActionInterface
            // that is why only GET requests allowed.
            return $.ajax({
                    url: $builder.get(0).URL,
                    type: 'GET',
                    data: requestData,
                    success: success,
                    showLoader: true
                });
        },

        fetchSettings: function ($el) {
            var requestData = {};

            requestData[$el.data('type')] = $el.data('name');

            return $.ajax({
                    url: this.options.settingsUrl,
                    data: requestData,
                    showLoader: true
                });
        },

        getBuilderDocument: function () {
            return this.$layout
                && $(this.$layout.find('iframe').get(0)?.contentDocument);
        },

        toggleBuilderBlocks: function () {
            const $builder = this.getBuilderDocument();

            $builder && $builder.find('body').toggleClass('show--allowed-blocks');
        }
    });

    return $.swissup.checkoutSuccessLayout2;
});
