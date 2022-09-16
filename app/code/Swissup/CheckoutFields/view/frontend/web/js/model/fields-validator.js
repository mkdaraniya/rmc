define(
    [
        'jquery',
        'uiRegistry',
        'Swissup_Checkout/js/scroll-to-error',
        'mage/validation'
    ],
    function ($, registry, scrollToError) {
        'use strict';

        var swissupCheckoutFieldsEnabled = window.checkoutConfig ?
                window.checkoutConfig.swissupCheckoutFieldsEnabled : {};

        return {
            /**
             * Validate checkout fields
             *
             * @returns {Boolean}
             */
            validate: function (hideError) {
                var checkoutProvider,
                    result;

                if (!swissupCheckoutFieldsEnabled || hideError) {
                    return true;
                }

                checkoutProvider = registry.get('checkoutProvider');

                if (typeof checkoutProvider.get('swissupCheckoutFields') === 'undefined') {
                    return true;
                }

                checkoutProvider.set('params.invalid', false);

                if (checkoutProvider.get('swissupCheckoutFields')) {
                    checkoutProvider.trigger('swissupCheckoutFields.data.validate');
                    result = !checkoutProvider.get('params.invalid');

                    if (!result) {
                        scrollToError();
                    }

                    return result;
                }

                return false;
            }
        };
    }
);
