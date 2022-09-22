define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Swissup_Checkout/js/model/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/step-navigator',
    'mage/validation'
], function (
    $,
    _,
    Component,
    registry,
    storage,
    quote,
    additionalValidators,
    stepNavigator
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_CheckoutRegistration/registration-form',
            formSelector: '.opc .form-login',
            checkbox: {
                visible: true,
                checked: false
            },
            pass: {
                minLength: 8,
                minCharacterSets: 3
            },
            listens: {
                checked: 'updateCheckboxState'
            }
        },

        /**
         * Move the component below email.
         */
        initialize: function () {
            this._super();

            this.placeFormAfterSelector();

            $(document).on('fc:validate-email-step', function (event, data) {
                if (data.isValid) {
                    data.isValid = this.validate();
                }
            }.bind(this));

            // Magento_InventoryInStorePickup integration
            registry.get('checkout.steps.store-pickup', function (storePickup) {
                var selector = '#store-pickup-customer-email-fieldset #store-pickup-checkout-customer-email';

                storePickup.isStorePickupSelected.subscribe(function (flag) {
                    this.placeFormAfterSelector(flag ? selector : false);
                }.bind(this));

                storePickup.isVisible.subscribe(function (flag) {
                    this.placeFormAfterSelector(
                        flag && storePickup.isStorePickupSelected() ?
                        selector :
                        false
                    );
                }.bind(this));

                if (storePickup.isVisible() && storePickup.isStorePickupSelected()) {
                    this.placeFormAfterSelector(selector);
                }
            }.bind(this));

            additionalValidators.registerValidator(this);
        },

        /** [moveFormAfter description] */
        placeFormAfterSelector: function (selector) {
            selector = selector || '#customer-email-fieldset #customer-email';

            if ($(selector).has('.checkout-registration').length) {
                return;
            }

            $.async(selector, function (emailField) {
                $.async('.checkout-registration', function (registration) {
                    $(emailField).closest('.field').after(registration);
                });
            });
        },

        /**
         * Calls initObservable of parent class, initializes observable
         * properties of instance.
         *
         * @return {Object} - reference to instance
         */
        initObservable: function () {
            this.vault = storage('checkout-registration');

            this._super()
                .observe({
                    checked: this.vault.get('checkbox', this.checkbox.checked),
                    password: '',
                    passwordConfirmation: ''
                });

            return this;
        },

        /**
         * Save checkbox state
         */
        updateCheckboxState: function () {
            this.vault.set('checkbox', this.checked());
        },

        /**
         * @return {Boolean}
         */
        isFormVisible: function () {
            var paths = [
                    'checkout.steps.email-step.customer-email',
                    quote.isVirtual() ?
                        'checkout.steps.billing-step.payment.customer-email' :
                        'checkout.steps.shipping-step.shippingAddress.customer-email'
                ],
                email;

            _.find(paths, function (path) {
                email = registry.get(path);

                if (email) {
                    return true;
                }
            });

            // hide form when email is already in use
            return email && !email.isPasswordVisible();
        },

        /**
         * @return {Boolean}
         */
        isPasswordVisible: function () {
            return !this.checkbox.visible || this.checked();
        },

        /**
         * @param {Object} component
         * @param {Object} event
         * @return {Boolean}
         */
        validate: function (component, event) {
            var el = event ? $(event.target) : $('#registration-password'),
                confirmation,
                activeStep = _.find(stepNavigator.steps(), function (step) {
                    return step.isVisible();
                }),
                result = {
                    isValid: false
                };

            if (!this.isFormVisible() ||
                !this.isPasswordVisible() ||
                // fix for F5 on the payment step
                !el.is(':visible') && activeStep && activeStep.code === 'payment'
            ) {
                return true;
            }

            $(this.formSelector).validation();

            $(el).validation();
            result.isValid = $(el).valid();

            if (result.isValid && $(el).attr('id') === 'registration-password') {
                confirmation = $('#registration-password-confirmation');

                if (!event || confirmation.val()) {
                    confirmation.validation();
                    result.isValid = confirmation.valid();
                }
            }

            $(document).trigger('checkout-registration:validate', result);

            return result.isValid;
        }
    });
});
