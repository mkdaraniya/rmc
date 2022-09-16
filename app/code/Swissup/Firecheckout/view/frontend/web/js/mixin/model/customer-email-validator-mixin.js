define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Swissup_Firecheckout/js/model/layout',
    'mage/validation'
], function ($, wrapper, quote, layout) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        target.validate = wrapper.wrap(
            target.validate,
            function (originalMethod, hideError) {
                var form = $('form[data-role=email-with-possible-login]:visible'),
                    validateEmail = $.validator.methods['validate-email'];

                // Don't validate email if it's not visible
                if (layout.isMultistep() && !quote.isVirtual() && !form.length) {
                    return true;
                }

                if (hideError) {
                    return validateEmail($('#customer-email').val());
                }

                return originalMethod();
            }
        );

        return target;
    };
});
