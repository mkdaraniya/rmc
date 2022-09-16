define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/action/select-payment-method'
], function (
    $,
    _,
    wrapper,
    quote,
    checkoutData,
    selectShippingMethodAction,
    paymentService,
    selectPaymentMethodAction
) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        target.resolveShippingRates = wrapper.wrap(
            target.resolveShippingRates,
            function (originalAction, ratesData) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                    defaultMethod = checkoutConfig.swissup.firecheckout.shipping.default_method,
                    availableRate = false;
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                if (ratesData.length === 1) {
                    $(document.body).addClass('fc-single-shipping-method');
                } else {
                    $(document.body).removeClass('fc-single-shipping-method');
                }

                originalAction.apply(
                    target,
                    Array.prototype.slice.call(arguments, 1)
                );

                if (!selectedShippingRate && defaultMethod) {
                    availableRate = _.find(ratesData, function (rate) {
                        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                        return rate.carrier_code + '_' + rate.method_code === defaultMethod;
                        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    });
                }

                if (!selectedShippingRate && !availableRate && ratesData.length === 1) {
                    availableRate = ratesData[0];
                }

                if (availableRate) {
                    selectShippingMethodAction(availableRate);
                }
            }
        );

        target.resolvePaymentMethod = wrapper.wrap(
            target.resolvePaymentMethod,
            function (originalAction) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                    selectedPaymentMethod = checkoutData.getSelectedPaymentMethod(),
                    defaultMethod = checkoutConfig.swissup.firecheckout.payment.default_method;
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                if (availablePaymentMethods.length === 1) {
                    $(document.body).addClass('fc-single-payment-method');
                } else {
                    $(document.body).removeClass('fc-single-payment-method');
                }

                originalAction();

                if (!selectedPaymentMethod && defaultMethod) {
                    availablePaymentMethods.some(function (payment) {
                        if (payment.method === defaultMethod) {
                            selectPaymentMethodAction(payment);

                            return true;
                        }
                    });
                }
            }
        );

        return target;
    };
});
