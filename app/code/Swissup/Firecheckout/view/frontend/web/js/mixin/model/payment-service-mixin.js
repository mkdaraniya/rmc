define([
    'jquery',
    'mage/utils/wrapper',
    'Swissup_Firecheckout/js/model/region-serializer'
], function ($, wrapper, regionSerializer) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        target.setPaymentMethods = wrapper.wrap(
            target.setPaymentMethods,
            function (originalAction, methods) {
                var data;

                if (this.doNotUpdate) {
                    // Do not update payments after place order was pressed
                    //
                    // This method is called after shipping information save:
                    // @see Checkout/view/frontend/web/js/model/shipping-save-processor/default::done
                    return;
                }

                // Save form values
                data = regionSerializer.serialize($('.payment-method._active'));

                originalAction(methods);

                // Restore form values
                setTimeout(function () {
                    regionSerializer.restore($('.payment-method._active'), data);
                }, 500);
            }
        );

        return target;
    };
});
