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
    'Swissup_Firecheckout/js/model/layout'
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
    layout
) {
    'use strict';

    return {
        /**
         * saveShippingMethod
         */
        saveShippingMethod: function () {
            var payload,
                shippingAddress;

            if (!quote.billingAddress() &&
                quote.shippingAddress() &&
                quote.shippingAddress().canUseForBilling()) {

                selectBillingAddressAction(quote.shippingAddress());
            }

            if (quote.shippingAddress().giftRegistryId) {
                shippingAddress = {
                    'extension_attributes': {
                        'gift_registry_id': quote.shippingAddress().giftRegistryId
                    }
                };
            } else {
                shippingAddress = quote.shippingAddress();
            }

            //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            payload = {
                addressInformation: {
                    'extension_attributes': {},
                    'shipping_address': shippingAddress,
                    'billing_address': quote.billingAddress(),
                    'shipping_method_code': quote.shippingMethod() ? quote.shippingMethod().method_code : '',
                    'shipping_carrier_code': quote.shippingMethod() ? quote.shippingMethod().carrier_code : ''
                }
            };
            //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

            totals.isLoading(true);

            return storage.post(
                resourceUrlManager.getUrlForSetShippingMethod(quote),
                JSON.stringify(payload)
            ).done(
                function (response) {
                    quote.setTotals(response.totals);

                    if (!layout.isMultistep()) {
                        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    }

                    totals.isLoading(false);
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    totals.isLoading(false);
                }
            );
        }
    };
});
