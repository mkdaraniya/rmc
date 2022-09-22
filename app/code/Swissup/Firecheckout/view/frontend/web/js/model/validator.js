define([
    'jquery',
    'underscore',
    'ko',
    'uiRegistry',
    'Swissup_Firecheckout/js/model/layout',
    'Swissup_Checkout/js/scroll-to-error',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/lib/validation/validator',
    'mage/translate'
], function (
    $,
    _,
    ko,
    registry,
    layout,
    scrollToError,
    quote,
    customer,
    paymentValidators,
    fieldValidator,
    $t
) {
    'use strict';

    return {
        /**
         * Validate firecheckout form
         */
        validate: function () {
            var isAddressValid     = layout.isMultistep() || this.validateShippingAddress(),
                isShippingSelected = isAddressValid && (layout.isMultistep() || this.validateShippingRadios()),
                isPaymentSelected  = this.validatePayment(),
                event = $.Event('fc:validate', {
                    valid: true
                });

            $('body').trigger(event);

            // try to scroll to third-party message
            setTimeout(scrollToError, 100);

            return event.valid &&
                isShippingSelected &&
                isPaymentSelected &&
                isAddressValid;
        },

        /**
         * @deprecated
         */
        scrollToError: function () {
            scrollToError();
        },

        /**
         * @return {Boolean}
         */
        validateShippingAddress: function () {
            if (quote.isVirtual()) {
                return true;
            }

            // Wyomind_Storepickup module integration (address form is hidden)
            if ((!customer.isLoggedIn() && !$('.form-shipping-address:visible').length) ||
                (customer.isLoggedIn() && !$('#checkout-step-shipping:visible').length)
            ) {
                return true;
            }

            return registry
                .get('checkout.steps.shipping-step.shippingAddress')
                .validateShippingInformation();
        },

        /**
         * Check is shipping radio is selected
         */
        validateShippingRadios: function () {
            var el = $('#co-shipping-method-form');

            if (!el.length) {
                return true;
            }

            this.removeNotice(el);

            if (!quote.shippingMethod() || typeof quote.shippingMethod() !== 'object') {
                this.addNotice(el, $t('Please specify a shipping method.'));

                return false;
            }

            return true;
        },

        /**
         * Check is payment is valid
         */
        validatePayment: function () {
            var el = $('#co-payment-form .payment-methods'),
                payment = quote.paymentMethod(),
                component,
                componentValid,
                form,
                formValid;

            if (!el.length) {
                return true;
            }

            this.removeNotice(el);

            if (!payment || typeof payment !== 'object') {
                this.addNotice(
                    el,
                    $t('Please specify a payment method.'),
                    el.find('.step-title')
                );

                return false;
            }

            component = registry.get(
                'checkout.steps.billing-step.payment.payments-list.' + payment.method
            );

            // StripeIntegration_Payments integration
            if (component &&
                component.isNewCard && component.isNewCard() &&
                component.stripePaymentsApplePayToken && !component.stripePaymentsApplePayToken() &&
                component.stripePaymentsStripeJsToken && !component.stripePaymentsStripeJsToken()
            ) {
                componentValid = true;
            }

            if (!componentValid) {
                componentValid = !component || component.validate();
            }

            form = $('.payment-method._active form');
            formValid = !form.length || !form.validation || form.validation().valid();

            return paymentValidators.validate() && componentValid && formValid;
        },

        /**
         * Add notice message at the top of the element
         *
         * @param {jQuery} el
         * @param {String} msg
         */
        addNotice: function (el, msg, after) {
            var method = 'prepend';

            if (after) {
                el = after;
                method = 'after';
            }

            el[method](
                '<div class="firecheckout-msg message notice"><span>' +
                    msg +
                '</span></div>'
            );
        },

        /**
         * Remove notice label
         *
         * @param {jQuery} el
         */
        removeNotice: function (el) {
            $('.firecheckout-msg', el).remove();
        },

        /**
         * Silently validate cmp and all nested cmp's
         *
         * @param {Component} cmp
         * @return {Boolean}
         */
        isValidUiComponent: function (cmp) {
            var self = this,
                result = true;

            if (cmp.elems) {
                result = _.every(cmp.elems(), function (el) {
                    return self.isValidUiComponent(el);
                });
            } else if (cmp.validate && cmp.value && cmp.visible && cmp.disabled) {
                result = !cmp.visible() || cmp.disabled() || fieldValidator(
                    cmp.validation,
                    cmp.value(),
                    cmp.validationParams
                ).passed;
            }

            return result;
        }
    };
});
