define([
    'uiRegistry'
], function (registry) {
    'use strict';

    return function (paymentData) {
        var paths = [
            'checkout.steps.shipping-step.shippingAddress.before-form.subscribe-at-checkout',
            'checkout.steps.billing-step.payment.beforeMethods.subscribe-at-checkout'
        ];

        paths.forEach(function (path) {
            registry.get(path, function (component) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                if (!paymentData.extension_attributes) {
                    paymentData.extension_attributes = {};
                }

                paymentData.extension_attributes.swissup_subscribe_at_checkout = component.checked();
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            });
        });
    };
});
