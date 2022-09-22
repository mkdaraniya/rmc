define([
    'jquery',
    'underscore',
    'uiComponent',
    'uiRegistry',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/shipping-service',
    'Swissup_Firecheckout/js/model/validator',
    'Magento_Checkout/js/model/address-converter',
    'Magento_Checkout/js/action/select-shipping-address',
    'Swissup_Firecheckout/js/action/sync-billing-address',
    'Swissup_Firecheckout/js/action/set-shipping-method',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Swissup_Firecheckout/js/plugin/fade-visible',
    'Swissup_Firecheckout/js/custom'
], function (
    $,
    _,
    Component,
    registry,
    quote,
    customer,
    paymentMethods,
    shippingService,
    validator,
    addressConverter,
    selectShippingAddress,
    syncBillingAddress,
    setShippingMethodAction,
    shippingRatesValidator
) {
    'use strict';

    return Component.extend({
        defaults: {
            plugins: {}
        },

        sameAsShippingObservers: [],

        /**
         * Initialize quote namespace with firecheckout storage
         *
         * @return void
         */
        initQuote: function () {
            quote.firecheckout = {
                state: {
                    initialPageLoad: true, // this flag means that page wasn't interacted yet
                    savingShippingMethod: false,
                    placeOrderPressed: false
                },
                // last selected values are stored in memo
                memo: {
                    shippingMethod: {},
                    shippingAddress: {}
                }
            };
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            this.initQuote();

            $(document).one('click keydown', function (event) {
                if (event.originalEvent && event.originalEvent.isTrusted !== false) {
                    quote.firecheckout.state.initialPageLoad = false;
                }
            });

            if (quote.isVirtual()) {
                $('body').addClass('firecheckout-quote-virtual');
            } else {
                this.addShippingHandlers();
            }

            quote.paymentMethod.subscribe(function () {
                validator.removeNotice('#co-payment-form');
            }, this);

            this.initPlugins();
        },

        /**
         * Run custom plugins
         */
        initPlugins: function () {
            _.each(this.plugins, function (el) {
                if (el.pluginDisabled || !el.plugin) {
                    return;
                }

                require([el.plugin], function (plugin) {
                    plugin.init.apply(plugin, el.params);
                });
            });
        },

        /**
         * Add shipping method/address related listeners
         */
        addShippingHandlers: function () {
            var debouncedSelectShippingAddress = _.debounce(function (address) {
                var currentAddress = quote.shippingAddress();

                if (currentAddress.getCacheKey() !== address.getCacheKey()) {
                    // some logic (shippingRatesValidator.validateFields) is
                    // already updated an address to the completely
                    // different object. Do not reset its cache_key as it will
                    // affect available shipping methods.

                    return;
                }

                selectShippingAddress(address);
            }, shippingRatesValidator.validateDelay + 70);

            this.debouncedSaveShippingMethod = _.debounce(
                this.saveShippingMethod.bind(this),
                200
            );

            quote.shippingMethod.subscribe(function () {
                validator.removeNotice('#co-shipping-method-form');

                if (shippingService.isLoading()) {
                    return;
                }
                this.applyShippingMethod(true);
            }, this);
            shippingService.isLoading.subscribe(function (flag) {
                if (flag) {
                    return;
                }
                this.applyShippingMethod();
            }, this);

            this.addSameAsShippingObservers();

            quote.paymentMethod.subscribe(function (method) {
                this.toggleEqualAddressClassName(method);
            }, this);

            // Call 'selectShippingAddress' while typing in the shipping address form
            if (!customer.isLoggedIn() || !customer.getShippingAddressList().length) {
                registry.async('checkoutProvider')(function (checkoutProvider) {
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        var shippingAddress = quote.shippingAddress(),
                            addressData = addressConverter.formAddressDataToQuoteAddress(shippingAddressData),
                            field;

                        if (!shippingAddress) {
                            return;
                        }

                        for (field in addressData) {
                            if (addressData.hasOwnProperty(field) &&
                                shippingAddress.hasOwnProperty(field) &&
                                typeof addressData[field] != 'function'
                            ) {
                                shippingAddress[field] = addressData[field];
                            }
                        }

                        debouncedSelectShippingAddress(shippingAddress);
                    });
                });
            }

            // Sync billing address on every 'selectShippingAddress'
            quote.shippingAddress.subscribe(syncBillingAddress);
        },

        /**
         * Apply shipping method
         */
        applyShippingMethod: function (force) {
            if (!shippingService.getShippingRates()().length) {
                quote.shippingMethod(null);
            }
            this.debouncedSaveShippingMethod(force);
        },

        /**
         * Saves shipping information with additional fixes:
         *  - check for ajax state in case of request is already sent
         *  - give 50ms time before making a request (for the form filler plugins)
         *
         * @return void
         */
        saveShippingMethod: function (force) {
            var methodSent;

            // prevent multiple ajax requests with the same parameters
            if (quote.firecheckout.state.savingShippingMethod !== false) {
                methodSent = quote.firecheckout.state.savingShippingMethod;

                if (!methodSent && !quote.shippingMethod()) {
                    return;
                }

                if (methodSent && quote.shippingMethod() &&
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    methodSent.method_code === quote.shippingMethod().method_code &&
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    methodSent.amount === quote.shippingMethod().amount) {

                    return;
                }
            }

            // shipping titles are updated by ko, so we shoud care about amount only
            if (!force && !this.shouldSaveShippingInformation() && !this.isTotalFieldUpdated()) {
                this._log('SKIP', quote.shippingMethod());

                return;
            }

            this._log('APPLY', quote.shippingMethod());
            quote.firecheckout.state.savingShippingMethod = quote.shippingMethod();

            // give some time for the form fillers
            setTimeout(function () {
                setShippingMethodAction()
                    .done(function () {
                        var address;

                        quote.firecheckout.state.savingShippingMethod = false;
                        quote.firecheckout.memo.shippingMethod = quote.shippingMethod();

                        address = quote.shippingAddress();
                        quote.firecheckout.memo.shippingAddress = {
                            'countryId': address.countryId,
                            'postcode': address.postcode,
                            'region': address.region,
                            'regionId': address.regionId,
                            'vatId': address.vatId
                        };
                    })
                    .fail(function () {
                        quote.firecheckout.state.savingShippingMethod = false;
                    });
            }, 50);
        },

        /**
         * Check if address should be saved
         * @return {Boolean}
         */
        shouldSaveShippingInformation: function () {
            var memo = quote.firecheckout.memo;

            if (!memo.shippingMethod && !quote.shippingMethod()) {
                // method is not available
                return false;
            }

            if (!memo.shippingMethod || !quote.shippingMethod()) {
                // method is changed from null to normal or vice versa
                return true;
            }

            if (this.isInitialPageLoad()) {
                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                if (quote.totals().shipping_amount == quote.shippingMethod().amount) { // eslint-disable-line
                    return false;
                }
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
            }

            if (memo.shippingMethod.amount !== quote.shippingMethod().amount) {
                return true;
            }

            return this.isTotalFieldUpdated();
        },

        /**
         * @return {Boolean}
         */
        isTotalFieldUpdated: function () {
            var memo = quote.firecheckout.memo,
                addressFields = [
                    'countryId',
                    'postcode',
                    'region',
                    'regionId',
                    'vatId'
                ];

            return addressFields.some(function (field) {
                return memo.shippingAddress[field] !== quote.shippingAddress()[field];
            });
        },

        /**
         * @return {Boolean}
         */
        isInitialPageLoad: function () {
            var memo = quote.firecheckout.memo,
                state = quote.firecheckout.state;

            return state.initialPageLoad &&
                _.isObject(memo.shippingMethod) &&
                _.isEmpty(memo.shippingMethod);
        },

        /**
         * Toggle 'equal-billing-shipping' classname according to
         * billingAddress.isAddressSameAsShipping
         *
         * @return boolean
         */
        toggleEqualAddressClassName: function (method) {
            registry.get(
                'checkout.steps.billing-step.payment.afterMethods.billing-address-form',
                function (billingAddress) {
                    billingAddress.isAddressSameAsShipping.subscribe(function (flag) {
                        $('body').toggleClass('equal-billing-shipping', flag);
                    });
                }
            );

            method = method || quote.paymentMethod();

            if (!method) {
                return;
            }
            registry.get(
                'checkout.steps.billing-step.payment.payments-list.' + method.method + '-form',
                function (billingAddress) {
                    var flag = billingAddress.isAddressSameAsShipping();

                    $('body').toggleClass('equal-billing-shipping', flag);
                }
            );

            return true;
        },

        /**
         * Add observers to each payment method to toggle 'equal-billing-shipping'
         * class name on "My billing and shipping address are the same" state update
         */
        addSameAsShippingObservers: function () {
            registry.get(
                'checkout.steps.billing-step.payment.afterMethods.billing-address-form',
                function (billingAddress) {
                    billingAddress.isAddressSameAsShipping.subscribe(function (flag) {
                        $('body').toggleClass('equal-billing-shipping', flag);
                    });
                }
            );

            _.each(paymentMethods(), function (paymentMethodData) {
                registry.get(
                    'checkout.steps.billing-step.payment.payments-list.' + paymentMethodData.method + '-form',
                    function (billingAddress) {
                        billingAddress.isAddressSameAsShipping.subscribe(function (flag) {
                            $('body').toggleClass('equal-billing-shipping', flag);
                        });
                    }
                );
            });
        },

        /**
         * @param  {String} title
         * @param  {Object} data
         */
        _log: function (title, data) {
            var date;

            if (window.location.hash !== '#development') {
                return;
            }

            date = new Date();
            console.log(date.getSeconds() + ': ' + title);
            console.log(data);
        }
    });
});
