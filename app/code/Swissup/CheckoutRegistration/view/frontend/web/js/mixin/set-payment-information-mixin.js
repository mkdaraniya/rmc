define([
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Swissup_CheckoutRegistration/js/model/data-assigner'
], function (wrapper, quote, assignData) {
    'use strict';

    return function (target) {
        return wrapper.wrap(target, function (originalAction, messageContainer, paymentData) {
            if (quote.isVirtual()) {
                assignData(paymentData);
            }

            return originalAction(messageContainer, paymentData);
        });
    };
});
