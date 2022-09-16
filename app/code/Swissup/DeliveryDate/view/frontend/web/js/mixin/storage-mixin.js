define([
    'jquery',
    'uiRegistry',
    'mage/utils/wrapper'
], function ($, registry, wrapper) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    /**
     * Compatibility magento < 2.2.2. PayloadExtender wasn't implemented.
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

        target.post = wrapper.wrap(
            target.post,
            function (o, url) {
                var data, deliveryDate;

                if (url.indexOf('/shipping-information') !== -1) {
                    data = JSON.parse(arguments[2]);

                    if (!data.addressInformation) {
                        data.addressInformation = {};
                    }

                    // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    if (!data.addressInformation.extension_attributes) {
                        data.addressInformation.extension_attributes = {};
                    }

                    deliveryDate = registry.get(
                        'checkout.steps.shipping-step.shippingAddress.delivery-date'
                    );

                    if (deliveryDate &&
                        deliveryDate.visible() &&
                        data.addressInformation.extension_attributes.delivery_date === undefined) {

                        data.addressInformation.extension_attributes.delivery_date =
                            deliveryDate.getValue('date');
                        data.addressInformation.extension_attributes.delivery_time =
                            deliveryDate.getValue('time');

                        arguments[2] = JSON.stringify(data);
                    }
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                }

                return o.apply(
                    target,
                    Array.prototype.slice.call(arguments, 1)
                );
            }
        );

        return target;
    };
});
