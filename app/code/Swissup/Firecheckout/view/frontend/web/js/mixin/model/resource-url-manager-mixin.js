define([], function () {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Save shipping method, without address validation.
         *
         * @param  {Object} quote
         * @return {String}
         */
        target.getUrlForSetShippingMethod = function (quote) {
            var urls = {
                    'guest': '/guest-carts/:cartId/shipping-method',
                    'customer': '/carts/mine/shipping-method'
                },
                params = {};

            if (this.getCheckoutMethod() === 'guest') {
                params.cartId = quote.getQuoteId();
            }

            return this.getUrl(urls, params);
        };

        /**
         * Save shipping address only to reload payment methods, if needed.
         * Shipping methods may not be available at this point.
         *
         * @param  {Object} quote
         * @return {String}
         */
        target.getUrlForSetShippingAddress = function (quote) {
            var urls = {
                    'guest': '/guest-carts/:cartId/shipping-address',
                    'customer': '/carts/mine/shipping-address'
                },
                params = {};

            if (this.getCheckoutMethod() === 'guest') {
                params.cartId = quote.getQuoteId();
            }

            return this.getUrl(urls, params);
        };

        return target;
    };
});
