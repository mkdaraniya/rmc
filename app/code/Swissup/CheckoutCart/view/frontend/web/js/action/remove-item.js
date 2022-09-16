define([
    'jquery',
    'mage/storage',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information',
    'Swissup_CheckoutCart/js/action/update-summary-heading',
    'Swissup_Checkout/js/action/update-shipping-rates'
], function (
    $,
    storage,
    customerData,
    resourceUrlManager,
    errorProcessor,
    fullScreenLoader,
    getPaymentInformationAction,
    updateSummaryHeadingAction,
    updateShippingRatesAction
) {
    'use strict';

    return function (quote, itemId) {
        fullScreenLoader.startLoader();

        return storage.delete(
            resourceUrlManager.getUrlForRemoveCartItem(quote, itemId)
        ).done(
            function () {
                var deferred = $.Deferred();

                // reload payment methods and totals
                getPaymentInformationAction(deferred);

                $.when(deferred).done(function () {
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    var totalQty = quote.totals().items_qty;
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                    // invalidate shopping cart
                    customerData.invalidate(['cart']);

                    if (Number(totalQty) === 0) {
                        window.location.reload();
                    } else {
                        // reload shipping methods
                        updateShippingRatesAction();

                        // update summary block heading quantity
                        updateSummaryHeadingAction(totalQty);

                        fullScreenLoader.stopLoader();
                    }
                });
            }
        ).fail(
            function (response) {
                errorProcessor.process(response);
                fullScreenLoader.stopLoader();
            }
        );
    };
});
