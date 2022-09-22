define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, customer, quote) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return wrapper.wrap(target, function (originalAction, messageContainer, paymentData, skipBilling) {
            var placeOrderPressed = quote.firecheckout && quote.firecheckout.state.placeOrderPressed,
                placeOrderVisible = $('.action.checkout', '.place-order').is(':visible'),
                paymentVisible = $('.checkout-payment-method').is(':visible');

            if (!customer.isLoggedIn() && !quote.guestEmail) {
                if (!paymentVisible || placeOrderVisible && !placeOrderPressed) {
                    quote.guestEmail = 'mail@example.com'; // Prevent '400 Bad Request' response
                    skipBilling = true;
                }
            }

            if (!placeOrderVisible && quote.guestEmail === 'mail@example.com') {
                quote.guestEmail = '';
            }

            return originalAction(messageContainer, paymentData, skipBilling);
        });
    };
});
