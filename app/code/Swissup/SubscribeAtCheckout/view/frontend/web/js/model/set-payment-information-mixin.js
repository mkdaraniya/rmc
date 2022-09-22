define([
    'mage/utils/wrapper',
    'Swissup_SubscribeAtCheckout/js/model/data-assigner'
], function (wrapper, dataAssigner) {
    'use strict';

    return function (setPaymentInformationAction) {
        return wrapper.wrap(
            setPaymentInformationAction,
            function (originalAction, messageContainer, paymentData) {
                dataAssigner(paymentData);

                return originalAction(messageContainer, paymentData);
            }
        );
    };
});
