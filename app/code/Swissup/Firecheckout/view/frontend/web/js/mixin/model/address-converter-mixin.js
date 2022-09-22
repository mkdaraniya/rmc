define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Added to fix js error with Magento_Braintree module:
         *     Cannot read property '0' of undefined.
         *     @see Magento_Braintree/js/view/payment/method-renderer/paypal.js#323
         *
         * Error occurs when:
         *  - defaultZipCode is set in backend panel
         *  - zip code is required
         *  - payment is rendered before customer entered any address data
         */
        target.formAddressDataToQuoteAddress = wrapper.wrap(
            target.formAddressDataToQuoteAddress,
            function (originalMethod, formData) {
                var data = $.extend(true, {}, formData);

                if (!data.street) {
                    data.street = [''];
                }

                return originalMethod(data);
            }
        );

        return target;
    };
});
