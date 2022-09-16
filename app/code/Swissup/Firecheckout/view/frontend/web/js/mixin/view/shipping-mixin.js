define([
    'jquery',
    'Swissup_Checkout/js/scroll-to-error'
], function ($, scrollToError) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var result = this._super(),
                    event = $.Event('fc:validate-shipping-information', {
                        valid: result
                    });

                $('body').trigger(event);

                // try to scroll to third-party message
                setTimeout(scrollToError, 100);

                return event.valid;
            }
        });
    };
});
