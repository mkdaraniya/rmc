define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig ||
            !checkoutConfig.swissup ||
            !checkoutConfig.swissup.geoip) {

            return target;
        }

        return wrapper.wrap(
            target,
            function (originalAction, addressData) {
                if (!addressData.region) {
                    addressData.region = {};
                }

                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                if (!addressData.region.region_id && checkoutConfig.defaultRegionId) {
                    addressData.region.region_id = checkoutConfig.defaultRegionId;
                    addressData.regionId = checkoutConfig.defaultRegionId;
                }
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                if (!addressData.city && checkoutConfig.defaultCity) {
                    addressData.city = checkoutConfig.defaultCity;
                }

                return originalAction(addressData);
            }
        );
    };
});
