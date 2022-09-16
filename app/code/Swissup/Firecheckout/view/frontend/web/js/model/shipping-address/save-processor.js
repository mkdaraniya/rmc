define([
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/resource-url-manager',
    'mage/storage',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/payment/method-converter',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/select-billing-address',
    'Swissup_Firecheckout/js/utils/loader'
], function (
    ko,
    quote,
    resourceUrlManager,
    storage,
    paymentService,
    methodConverter,
    errorProcessor,
    totals,
    selectBillingAddressAction,
    loader
) {
    'use strict';

    return {
        /**
         * saveShippingAddress. shipping method may not be set at this point
         */
        saveShippingAddress: function () {
            var payload;

            if (!quote.billingAddress() &&
                quote.shippingAddress() &&
                quote.shippingAddress().canUseForBilling()) {

                selectBillingAddressAction(quote.shippingAddress());
            }

            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            payload = {
                addressInformation: {
                    'extension_attributes': {},
                    'shipping_address': quote.shippingAddress(),
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod() ? quote.shippingMethod().method_code : null,
                    'shipping_carrier_code': quote.shippingMethod() ? quote.shippingMethod().carrier_code : null
                }
            };
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            totals.isLoading(true);
            loader('.checkout-payment-method').show();

            return storage.post(
                resourceUrlManager.getUrlForSetShippingAddress(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    totals.isLoading(false);
                    loader('.checkout-payment-method').hide();
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    totals.isLoading(false);
                    loader('.checkout-payment-method').hide();
                }
            );
        }
    };
});
