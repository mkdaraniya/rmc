define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    'uiComponent',
    'Swissup_Firecheckout/js/utils/move',
    'mage/translate',
    'mage/collapsible'
], function ($, _, ko, Component, move, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_FirecheckoutIntegrations/magento-giftoptions',
            orderLevelTitle: 'Gift options for Entire Order'
        },
        visible: ko.observable(false),

        /**
         * [initialize description]
         */
        initialize: function () {
            var self = this;

            $.async('#gift-options-cart [data-role="title"]', function (title) {
                self.visible(true);
                $(title).find('span').text($t(self.orderLevelTitle));
                move('#gift-options-cart')
                    .prepend('.fc-magento-giftoptions > [data-role="content"]');
            });

            return this._super();
        },

        /**
         * @return {Boolean}
         */
        isVisible: function () {
            if (this.visible()) {
                return true;
            }

            return _.some(this.elems(), function (el) {
                return !el.isVisible || el.isVisible();
            });
        },

        /**
         * Prepare content
         */
        onElementRender: function (el) {
            this.createCollapsible(el);
        },

        /**
         * Create colapsible element
         *
         * @param {Element} el
         */
        createCollapsible: function (el) {
            $(el).collapsible({
                openedState: '_active'
            });

            $(el).on('beforeOpen', function () {
                var children = $(el).find('> [data-role="content"]').children();

                if (children.length === 1 &&
                    children.find('[data-role="content"]:visible').length === 0) {

                    children.find('[data-role="title"]').trigger('click');
                }
            });
        }
    });
});
