define([
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information'
], function ($, fullScreenLoader, getPaymentInformationAction) {
    'use strict';

    function updateTotals() {
        var deferred = $.Deferred();

        fullScreenLoader.startLoader();

        getPaymentInformationAction(deferred);

        $.when(deferred).done(function () {
            fullScreenLoader.stopLoader();
        });
    }

    return {
        /**
         * Init plugin
         */
        init: function () {
            $(document).ajaxComplete(function(event, xhr, settings) {
                if (settings &&
                    settings.url.indexOf('/multifees/checkout/fee') !== -1
                ) {
                    updateTotals();
                }
            })
        }
    };
});
