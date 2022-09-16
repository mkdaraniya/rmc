define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiComponent',
    'uiRegistry'
], function ($, Component, registry) {
    'use strict';

    var paths = [
        'checkout.steps.email-step.customer-email',
        'checkout.steps.shipping-step.shippingAddress.customer-email',
        'checkout.steps.billing-step.payment.customer-email'
    ];

    paths.forEach(function (path) {
        registry.get(path, function (email) {
            email.isLoading(false);
            // eslint-disable-next-line max-nested-callbacks
            email.isLoading.subscribe(function (value) {
                if (value) {
                    email.isLoading(false);
                }
            });

            email.isPasswordVisible(false);

            // eslint-disable-next-line max-nested-callbacks
            email.isPasswordVisible.subscribe(function (value) {
                if (value) {
                    email.isPasswordVisible(false);
                }
            });
        });
    });

    // prevent Chrome from asking about saving customer password
    $.async('.opc .form-login #customer-password', function (el) {
        $(el).closest('.fieldset').remove();
    });

    return Component.extend({});
});
