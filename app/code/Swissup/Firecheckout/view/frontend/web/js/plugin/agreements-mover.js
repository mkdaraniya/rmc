define([
    'Magento_Ui/js/lib/view/utils/async',
    'Swissup_Firecheckout/js/utils/form-field/watcher',
    'Magento_Checkout/js/model/quote'
], function ($, watcher, quote) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        agreementsConfig = checkoutConfig ? checkoutConfig.checkoutAgreements : {},
        config = {
            title: '',
            position: 'above-place-order'
        };

    /**
     * Create container for agreement clones
     */
    function createContainer() {
        var mapping = {
            'above-place-order': '.order-review-form',
            'above-cart-items': '.block.items-in-cart'
        };

        if (!mapping[config.position]) {
            return false;
        }

        $.async(mapping[config.position], function (el) {
            $(el).before([
                '<div class="payment-method agreements-clone" style="display:none">',
                    config.title ? '<div class="fc-heading">' + config.title + '</div>' : '',
                    '<div class="fc-agreements-container"></div>',
                '</div>'
            ].join(''));
        });

        return true;
    }

    /**
     * Copy active payment method agreements to the order summary section
     */
    function copyAgreements() {
        var container = $('.fc-agreements-container'),
            agreements = $('.payment-method._active').find('.checkout-agreements-block'),
            form = agreements.closest('form'),
            hiddenOrderButton = $([
                    '.actions-toolbar:not([style="display: none;"])',
                    '.action.checkout:not([style="display: none;"])'
                ].join(' '),
                '.payment-method._active'
            );

        container.empty();

        if (!container.length ||
            !agreements.length ||
            !hiddenOrderButton.length ||
            !form.hasClass('payments')
        ) {
            $('body').removeClass('fc-agreements-moved');
            $('.agreements-clone').hide();

            return;
        }

        $('body').addClass('fc-agreements-moved');
        $('.agreements-clone').css('display', '');
        agreements.clone(true).appendTo(container);
    }

    /**
     * Sync value between cloned and original checkboxes
     */
    function initObservers() {
        watcher('.agreements-clone input', function (falsy, el) {
            var name = $(el).attr('name'),
                checked = $(el).prop('checked');
            $('[name="' + name + '"]').prop('checked', checked);
        });

        // copy agreements on initial page load
        $.async('.payment-method._active div[data-role=checkout-agreements]', function () {
            $.async('.order-review-form', copyAgreements);
        });

        // update agreements on payment change
        quote.paymentMethod.subscribe(function () {
            $('body').addClass('fc-agreements-moved');
            setTimeout(copyAgreements, 200); // wait until '_active' class will be added
        });

        // update clone on dom changes (validation)
        (function () {
            var observer = new MutationObserver(function (mutations) {
                var mutation = mutations[0];

                if (!$(mutation.target).parents('.payment-method._active').length) {
                    return;
                }

                if (['childList', 'attributes'].indexOf(mutation.type) > -1) {
                    copyAgreements();
                }
            });

            $.async('.opc-payment .checkout-agreements-block', function (el) {
                observer.observe(el, {
                    childList: true,
                    attributes: true,
                    subtree: true
                });
            });
        })();
    }

    return {
        /**
         * @param {Object} settings
         */
        init: function (settings) {
            if (!agreementsConfig.agreements || !agreementsConfig.agreements.length) {
                return;
            }

            config = $.extend(config, settings || {});

            if (createContainer()) {
                initObservers();
            }
        }
    };
});
