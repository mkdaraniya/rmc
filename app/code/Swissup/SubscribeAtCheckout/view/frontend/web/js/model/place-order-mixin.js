define([
    'mage/utils/wrapper',
    'Swissup_SubscribeAtCheckout/js/model/data-assigner'
], function (wrapper, dataAssigner) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(
            placeOrderAction,
            function (originalAction, paymentData, messageContainer) {
                dataAssigner(paymentData);

                return originalAction(paymentData, messageContainer);
            }
        );
    };
});
