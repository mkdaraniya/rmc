define([
    'mage/utils/wrapper',
    'Swissup_CheckoutFields/js/model/checkout-fields-assigner'
], function (wrapper, checkoutFieldsAssigner) {
    'use strict';

    return function (setPaymentInformationAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(setPaymentInformationAction, function (
            originalAction,
            messageContainer,
            paymentData,
            skipBilling
        ) {
            checkoutFieldsAssigner(paymentData);

            return originalAction(messageContainer, paymentData, skipBilling);
        });
    };
});
