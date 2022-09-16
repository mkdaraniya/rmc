define([
    'mage/utils/wrapper',
    'Swissup_CheckoutRegistration/js/model/data-assigner'
], function (wrapper, assignData) {
    'use strict';

    /**
     * This file works on Magento >= 2.2.2 only.
     *
     * @param  {Object} target
     * @return {Object}
     */
    return function (target) {
        return wrapper.wrap(target, function (o, payload) {
            o(payload);

            assignData(payload.addressInformation);

            return payload;
        });
    };
});
