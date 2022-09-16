define([
    'uiRegistry',
    'Magento_Checkout/js/model/step-navigator'
], function (registry, stepNavigator) {
    'use strict';

    return function (target) {
        return target.extend({
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var form = registry.get(
                        'checkout.steps.email-step.before-form.checkout-registration',
                        'checkout.steps.shipping-step.shippingAddress.before-form.checkout-registration'
                    ),
                    parentResult = this._super(),
                    localResult = true;

                if (form && form.isFormVisible()) {
                    localResult = form.validate();

                    if (!localResult &&
                        form.name === 'checkout.steps.email-step.before-form.checkout-registration'
                    ) {
                        stepNavigator.navigateTo('email-address');
                    }
                }

                return parentResult && localResult;
            }
        });
    };
});
