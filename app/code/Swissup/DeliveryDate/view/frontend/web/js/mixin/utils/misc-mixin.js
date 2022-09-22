define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig ||
            !checkoutConfig.swissup ||
            !checkoutConfig.swissup.DeliveryDate ||
            !checkoutConfig.swissup.DeliveryDate.enabled) {

            return target;
        }

        target.convertToMomentFormat = wrapper.wrap(
            target.convertToMomentFormat,
            function (originalMethod, format) {
                format = originalMethod(format);
                format = format.replace(/mm|m/g, 'MM');  // month is `MM` in moment.js
                format = format.replace('EEEE', 'dddd'); // Fullday is `dddd` in moment.js
                format = format.replace('EEE', 'ddd');   // Shortday is `ddd` in moment.js

                return format;
            }
        );

        return target;
    };
});
