define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Swissup_Firecheckout/js/model/validator'
], function ($, _, ko, registry, quote, checkoutData, paymentMethods, additionalValidators, validator) {
    'use strict';

    /**
     * Update billing address instantly after field update.
     * Used when "Update" address button is hidden.
     */
    return {
        /** [init description] */
        init: function () {
            $('body').addClass('fc-billing-instant-save');

            this.fillAddressForm();
            this.overrideFormVisibility();
            this.addObservers();

            additionalValidators.registerValidator(this);
        },

        /** [fillAddressForm description] */
        fillAddressForm: function () {
            var data = window.checkoutConfig.billingAddressFromData ||
                checkoutData.getNewCustomerBillingAddress() ||
                {};

            _.each(this.flattenData(data), function (value, key) {
                var selector = '.checkout-billing-address [name="' + key + '"]';

                $.async(selector, function (el) {
                    $(el).val(value).trigger('change');
                });
            });
        },

        /** [flattenData description] */
        flattenData: function (data, parentKey) {
            var self = this,
                result = {};

            _.each(data, function (value, key) {
                key = parentKey ? parentKey + '[' + key + ']' : key;

                if (_.isArray(value) || _.isObject(value)) {
                    result = _.extend(result, self.flattenData(value, key));
                } else {
                    result[key] = value;
                }
            });

            return result;
        },

        /** [addObservers description] */
        addObservers: function () {
            var self = this,
                scopes = [
                    '.checkout-billing-address input',
                    '.checkout-billing-address select',
                    '.checkout-billing-address textarea'
                ],
                debouncedAddressUpdate = _.debounce(self.updateAddress.bind(this), 300);

            $(document).on('change paste cut', scopes.join(','), function () {
                debouncedAddressUpdate(this);
            });
        },

        /** [updateAddress description] */
        updateAddress: function (element) {
            var address = this.getBillingAddress(),
                selectedAddress = address.selectedAddress(),
                shippingAddress = quote.shippingAddress();

            if (!address) {
                return;
            }

            if (!quote.isVirtual()) {
                if (!shippingAddress || address.isAddressSameAsShipping()) {
                    // when address is the same magento will save address on its own
                    return;
                }

                // do not update address when uncheck the checkbox and selected address is the same
                if (element.name === 'billing-address-same-as-shipping' &&
                    selectedAddress &&
                    selectedAddress.customerAddressId === shippingAddress.customerAddressId
                ) {
                    return;
                }
            }

            if (address.isAddressFormVisible() && !this.validate(address)) {
                return;
            }

            address.updateAddress();
        },

        /** [getBillingAddress description] */
        getBillingAddress: function () {
            var address = registry.get('checkout.steps.billing-step.payment.afterMethods.billing-address-form'),
                payment = quote.paymentMethod();

            if (address) {
                return address;
            }

            if (!payment || !payment.method) {
                return false;
            }

            return registry.get('checkout.steps.billing-step.payment.payments-list.' + payment.method + '-form');
        },

        /** [overrideFormVisibility description] */
        overrideFormVisibility: function () {
            registry.get(
                'checkout.steps.billing-step.payment.afterMethods.billing-address-form',
                this.wrapBillingAddress
            );

            _.each(paymentMethods(), function (payment) {
                registry.get(
                    'checkout.steps.billing-step.payment.payments-list.' + payment.method + '-form',
                    this.wrapBillingAddress
                );
            }.bind(this));
        },

        /** [wrapBillingAddress description] */
        wrapBillingAddress: function (address) {
            var isAddressDetailsVisible = address.isAddressDetailsVisible,
                isAddressSameAsShipping = address.isAddressSameAsShipping;

            address.isAddressDetailsVisible = ko.pureComputed({
                /** [read description] */
                read: function () {
                    return isAddressSameAsShipping() ? true : false;
                },

                /** [write description] */
                write: function () {
                    isAddressDetailsVisible(false);
                }
            });
        },

        /** [validate description] */
        validate: function (hideError) {
            var address = this.getBillingAddress();

            if (address.isAddressSameAsShipping() || !address.isAddressFormVisible()) {
                return true;
            }

            if (hideError) {
                return validator.isValidUiComponent(address);
            }

            address.source.set('params.invalid', false);
            address.source.trigger(address.dataScopePrefix + '.data.validate');

            if (address.source.get(address.dataScopePrefix + '.custom_attributes')) {
                address.source.trigger(address.dataScopePrefix + '.custom_attributes.data.validate');
            }

            return !address.source.get('params.invalid');
        }
    };
});
