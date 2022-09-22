define([
    'underscore',
    'uiCollection',
    'Magento_Checkout/js/model/quote'
], function (_, Collection, quote) {
    'use strict';

    return Collection.extend({
        defaults: {
            visible: true,
            template: 'Swissup_DeliveryDate/container',
            filterPerShippingMethod: false,
            shippingMethods: ''
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super();

            this.observe('visible');

            return this;
        },

        /**
         * @return {Element}
         */
        initialize: function () {
            this._super();

            if (this.filterPerShippingMethod) {
                this.addShippingMethodObserver();
            }

            return this;
        },

        /**
         * @param  {String} key
         * @return {String}
         */
        getValue: function (key) {
            var field = this.getChild(key),
                result = '';

            if (field) {
                result = field.value();
            }

            return result;
        },

        /**
         * Add observer to show/hide delivery date field
         */
        addShippingMethodObserver: function () {
            quote.shippingMethod.subscribe(function (method) {
                var shippingMethods = this.shippingMethods || '',
                    methodType;

                if (!method) {
                    return this.visible(false);
                }

                //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                methodType = method.carrier_code + '_' + method.method_code;
                //jscs:enable requireCamelCaseOrUpperCaseIdentifiers

                if (shippingMethods.indexOf(methodType) !== -1) {
                    this.visible(true);
                } else {
                    this.visible(false);
                }
            }, this);
        },

        /**
         * @return {Object}
         */
        validate: function () {
            var isValid = true;

            if (this.visible()) {
                _.each(this.elems(), function (el) {
                    if (!el.validate().valid) {
                        isValid = false;
                    }
                });
            }

            return {
                valid: isValid,
                target: this
            };
        }
    });
});
