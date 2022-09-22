define([
    'underscore',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/action/select-billing-address'
], function (_, registry, quote, paymentMethods, selectBillingAddress) {
    'use strict';

    return function () {
        var method = quote.paymentMethod(),
            sharedBillingAddress = 'checkout.steps.billing-step.payment.afterMethods.billing-address-form',
            billingAddressNames = [],
            billingNameForCurrentPayment;

        if (registry.get(sharedBillingAddress)) {
            billingAddressNames.push(sharedBillingAddress);
        } else if (!method) {
            _.each(paymentMethods(), function (paymentMethodData) {
                billingAddressNames.push(
                    'checkout.steps.billing-step.payment.payments-list.' +
                    paymentMethodData.method +
                    '-form'
                );
            });
        } else {
            billingNameForCurrentPayment =
                'checkout.steps.billing-step.payment.payments-list.' +
                method.method +
                '-form';

            billingAddressNames.push(billingNameForCurrentPayment);

            // fix for paypal express and other payments without visible address
            if (!registry.get(billingNameForCurrentPayment) &&
                quote.shippingAddress() &&
                quote.shippingAddress().canUseForBilling()) {

                selectBillingAddress(quote.shippingAddress());
            }
        }

        _.each(billingAddressNames, function (name) {
            registry.get(name, function (billingAddress) {
                if (!billingAddress.isAddressSameAsShipping()) {
                    return;
                }

                if (quote.shippingAddress() &&
                    quote.shippingAddress().canUseForBilling()) {

                    selectBillingAddress(quote.shippingAddress());
                } else if (billingAddress.isAddressSameAsShipping()) {
                    // GiftRegistry fix: show "Edit" button below billing address
                    billingAddress.isAddressSameAsShipping(false);
                }
            });
        });
    };
});
