define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function (wrapper, quote) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Don't set billing address if "Place Order" was just pressed (2.2.8 bugfix)
         */
        return wrapper.wrap(target, function (originalAction, billingAddress) {
            if (quote.firecheckout && quote.firecheckout.state.placeOrderPressed) {
                // 2.2.8 compatiblity to fix equal billing and shipping addresses
                return;
            }

            originalAction(billingAddress);
        });
    };
});
