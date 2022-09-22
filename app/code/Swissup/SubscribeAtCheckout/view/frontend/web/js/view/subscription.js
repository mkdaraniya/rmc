define([
    'Magento_Ui/js/lib/view/utils/async',
    'uiRegistry',
    'Magento_Ui/js/form/element/boolean',
    'Swissup_Checkout/js/model/storage'
], function ($, registry, Component, storage) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_SubscribeAtCheckout/form/element/subscription',
            listens: {
                value: 'updateCheckboxState'
            }
        },

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();

            // Move subscription to the login section for better compatiblity
            // with store-pickup modules that hide shipping section
            this.placeFormAfterSelector();

            // Magento_InventoryInStorePickup integration
            registry.get('checkout.steps.store-pickup', function (storePickup) {
                var selector = '#store-pickup-customer-email-fieldset';

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
        },

        /** [moveFormAfter description] */
        placeFormAfterSelector: function (selector) {
            var self = this,
                form = '#' + self.uid;

            selector = selector || '#customer-email-fieldset';

            if ($(selector).has(form).length) {
                return;
            }

            $.async(selector, function (emailFieldset) {
                $.async(form, function (subscription) {
                    $(emailFieldset).append($(subscription).closest('.subscription').get(0));
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
            this.vault = storage('subscribe-at-checkout');

            this._super();
            this.value(this.vault.get('checkbox', this.value()));

            return this;
        },

        /**
         * Save checkbox state
         */
        updateCheckboxState: function () {
            this.vault.set('checkbox', this.value());
        }
    });
});
