define([
    'underscore',
    'uiRegistry'
], function (_, registry) {
    'use strict';

    return function (container) {
        var paths = [
                'checkout.steps.email-step.before-form.checkout-registration',
                'checkout.steps.shipping-step.shippingAddress.before-form.checkout-registration',
                'checkout.steps.billing-step.payment.beforeMethods.checkout-registration'
            ],
            form;

        _.find(paths, function (path) {
            form = registry.get(path);

            if (form) {
                return true;
            }
        });

        if (!form || !form.isFormVisible()) {
            return;
        }

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        container.extension_attributes = _.extend(
            container.extension_attributes || {},
            {
                registration_checkbox_state: form.checked(),
                registration_password: form.password(),
                registration_password_confirmation: form.passwordConfirmation()
            }
        );
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
    };
});
