define([], function () {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig ||
            !checkoutConfig.swissup ||
            !checkoutConfig.swissup.CheckoutCart ||
            !checkoutConfig.swissup.CheckoutCart.enabled
        ) {
            return target;
        }

        /**
         * @param  {Object} quote
         * @return {String}
         */
        target.getUrlForUpdateCartItems = function (quote) {
            var params = {},
                urls = {
                    'guest': '/guest-carts/:cartId/items',
                    'customer': '/carts/mine/items'
                };

            if (this.getCheckoutMethod() === 'guest') {
                params.cartId = quote.getQuoteId();
            }

            return this.getUrl(urls, params);
        };

        /**
         * @param  {Object} quote
         * @param  {Number} itemId
         * @return {String}
         */
        target.getUrlForRemoveCartItem = function (quote, itemId) {
            var params = {
                    itemId: itemId
                },
                urls = {
                    'guest': '/guest-carts/:cartId/items/:itemId',
                    'customer': '/carts/mine/items/:itemId'
                };

            if (this.getCheckoutMethod() === 'guest') {
                params.cartId = quote.getQuoteId();
            }

            return this.getUrl(urls, params);
        };

        return target;
    };
});
