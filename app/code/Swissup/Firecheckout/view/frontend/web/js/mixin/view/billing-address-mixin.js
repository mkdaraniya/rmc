define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/address-converter'
], function ($, quote, addressConverter) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        updateTriggered = false,
        lastShippingAddress = {};

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * Mark updateTriggered flag. Used in updateAddresses method.
             */
            updateAddress: function () {
                updateTriggered = true;
                this._super();
            },

            /**
             * Don't save billing address when it has invalid fields.
             *
             * Fixes ajax reload when address is rendered outside of
             * payment method container.
             */
            updateAddresses: function () {
                if (updateTriggered) {
                    updateTriggered = false;

                    if (this.source.get('params.invalid')) {
                        return;
                    }
                }

                this._super();
            },

            /**
             * Change logic to for billing before shipping position
             */
            useShippingAddress: function () {
                if ($('body').hasClass('fc-billing-before-shipping') && !this.selectedAddress()) {
                    this.useForShippingAddress();

                    if (!this.isAddressSameAsShipping()) {
                        return true;
                    }
                }

                return this._super();
            },

            /**
             * Make billing address in charge. When check "Use same address" - copy billing to shipping.
             */
            useForShippingAddress: function () {
                var billingScope = '.billing-address-form',
                    shippingScope = '.form-shipping-address',
                    sameAsShipping = this.isAddressSameAsShipping(),
                    billingAddress = quote.billingAddress(),
                    copyFrom = sameAsShipping ? billingScope : shippingScope,
                    copyTo = sameAsShipping ? shippingScope : billingScope;

                $('input[name], select[name], textarea[name]', copyFrom).each(function () {
                    var el = $('[name="' + this.name + '"]', copyTo),
                        checkable = ['checkbox', 'radio'].indexOf(el.attr('type')) > -1;

                    if (sameAsShipping) {
                        lastShippingAddress[this.name] = checkable ? el.prop('checked') : el.val();
                    }

                    if (checkable) {
                        el.prop('checked', $(this).prop('checked'));
                    } else {
                        el.val($(this).val())
                    }

                    el.trigger('change');
                });

                if (!sameAsShipping) {
                    // restore old shipping address in shipping form
                    $.each(lastShippingAddress, function (name, value) {
                        var el = $('[name="' + name + '"]', shippingScope),
                            checkable = ['checkbox', 'radio'].indexOf(el.attr('type')) > -1;

                        if (checkable) {
                            el.prop('checked', value);
                        } else {
                            el.val(value);
                        }

                        if (!el.attr('aria-required') || value || checkable) {
                            el.trigger('change');
                        }
                    });

                    // create new billing address to produce unique getCacheKey
                    quote.billingAddress(
                        addressConverter.formAddressDataToQuoteAddress(
                            addressConverter.quoteAddressToFormAddressData(billingAddress)
                        )
                    );
                }

                this.isAddressDetailsVisible(sameAsShipping);

                return true;
            }
        });
    };
});
