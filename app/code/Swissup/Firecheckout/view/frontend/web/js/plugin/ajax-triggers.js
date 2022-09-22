define([
    'Swissup_Firecheckout/js/model/layout',
    'Swissup_Firecheckout/js/model/storage-sequence',
    'Swissup_Checkout/js/action/update-shipping-rates'
], function (layout, sequence, updateShippingRates) {
    'use strict';

    return {
        /**
         * Add triggers to specific requests
         */
        init: function () {
            /**
             * Add sequence after coupons request to reload shipping methods
             */
            sequence.add('after', '/coupons', function (result) {
                var isSuccess = true;

                if (!result) {
                    return;
                }

                if (result.status) {
                    isSuccess = result.status >= 200 &&
                                result.status < 300 ||
                                result.status === 304;
                }

                if (isSuccess) {
                    updateShippingRates();
                }
            });
        }
    };
});
