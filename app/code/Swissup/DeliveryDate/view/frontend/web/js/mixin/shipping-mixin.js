define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    'use strict';

    return function (target) {
        if (!window.checkoutConfig ||
            !window.checkoutConfig.swissup ||
            !window.checkoutConfig.swissup.DeliveryDate ||
            !window.checkoutConfig.swissup.DeliveryDate.enabled) {

            return target;
        }

        return target.extend({
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var deliveryDate = registry.get(
                        'checkout.steps.shipping-step.shippingAddress.delivery-date'
                    ),
                    parentResult = this._super(),
                    localResult = true;

                if (deliveryDate && deliveryDate.visible()) {
                    localResult = deliveryDate.validate().valid;
                }

                return parentResult && localResult;
            }
        });
    };
});
