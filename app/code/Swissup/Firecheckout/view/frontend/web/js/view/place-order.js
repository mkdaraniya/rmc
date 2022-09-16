define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Swissup_Firecheckout/js/model/place-order'
], function ($, ko, Component, quote, placeOrderModel) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_Firecheckout/place-order'
        },
        message: ko.observable(false),

        /**
         * Initialize component
         */
        initialize: function () {
            var syncButtonState = this.syncButtonState.bind(this);

            this._super();

            quote.paymentMethod.subscribe(function () {
                setTimeout(syncButtonState, 50);
            });

            // Virtual quote and Bread_BreadCheckout compatibility
            setInterval(syncButtonState, 1000);
        },

        /**
         * Triggers place order methods
         */
        placeOrder: function () {
            placeOrderModel.placeOrder();
        },

        /**
         * Checks is block is a last one at the moment of initialization.
         * Fixes jumping button on light theme, when loader is appended to the block.
         *
         * @return {Boolean}
         */
        isLast: function () {
            var block = $('.place-order', '.opc-block-summary');

            return block.is(':last-child') || block.next('.loading-mask').length;
        },

        /**
         * Sync button title and attributes.
         * Improves third-party modules compatibility.
         */
        syncButtonState: function () {
            var placeOrder = $('.action.checkout', '.place-order'),
                // span = placeOrder.find('span').first(),
                method,
                button;

            // restore default values
            // if (span.attr('data-fc-text')) {
            //     span.text(span.attr('data-fc-text'));
            // }
            placeOrder.show();
            placeOrder.prop('disabled', false);
            placeOrder.removeClass('disabled');

            method = quote.paymentMethod();

            if (!method || !method.method) {
                return;
            }

            button = this._getVisiblePlaceOrderButton();

            if (button.length &&
                (quote.isVirtual() || this._getVisibleSaveShippingButton().length)) {
                // if (!span.attr('data-fc-text')) {
                //     span.attr('data-fc-text', placeOrder.find('span').text());
                // }
                // span.text(button.text());

                placeOrder.show();
                placeOrder.prop('disabled', button.prop('disabled'));
                placeOrder.toggleClass('disabled', button.hasClass('disabled'));
            } else {
                placeOrder.hide();
            }
        },

        /**
         * Try to find original *visible* "Place Order" button.
         * @return {jQuery}
         */
        _getVisiblePlaceOrderButton: function () {
            return $(
                [
                    '.actions-toolbar:not([style="display: none;"])',
                    '.action.checkout:not([style="display: none;"])'
                ].join(' '),
                $('.payment-method._active')
            );
        },

        /**
         * Try to find original *visible* "Continue" button.
         * @return {jQuery}
         */
        _getVisibleSaveShippingButton: function () {
            return $(
                [
                    '.actions-toolbar:not([style="display: none;"])',
                    '.action.continue:not([style="display: none;"])'
                ].join(' '),
                $('.checkout-shipping-method')
            );
        }
    });
});
