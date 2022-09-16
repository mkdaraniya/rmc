define([
    'mage/utils/wrapper',
    'Swissup_Checkout/js/scroll-to-error'
], function (wrapper, scrollToError) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        target.process = wrapper.wrap(
            target.process,
            function (originalMethod, response, messageContainer) {
                var result = originalMethod(response, messageContainer);

                scrollToError();

                return result;
            }
        );

        return target;
    };
});
