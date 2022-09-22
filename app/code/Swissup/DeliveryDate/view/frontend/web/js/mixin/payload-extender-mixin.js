define([
    'jquery',
    'uiRegistry',
    'mage/utils/wrapper'
], function ($, registry, wrapper) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    /**
     * This file works on Magento >= 2.2.2 only.
     *
     * @param  {Object} target
     * @return {Object}
     */
    return function (target) {

        if (!checkoutConfig ||
            !checkoutConfig.swissup ||
            !checkoutConfig.swissup.DeliveryDate ||
            !checkoutConfig.swissup.DeliveryDate.enabled) {

            return target;
        }

        target = wrapper.wrap(
            target,
            function (o, payload) {
                var deliveryDate = registry.get(
                    'checkout.steps.shipping-step.shippingAddress.delivery-date'
                );

                o(payload);

                if (deliveryDate && deliveryDate.visible()) {
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    payload.addressInformation.extension_attributes.delivery_date =
                        deliveryDate.getValue('date');
                    payload.addressInformation.extension_attributes.delivery_time =
                        deliveryDate.getValue('time');
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                }

                return payload;
            }
        );

        return target;
    };
});
