define([
    'Magento_Ui/js/lib/view/utils/async',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/address-list',
    'Swissup_Checkout/js/is-in-viewport',
    'mage/translate'
], function ($, ko, Component, quote, addressList, isInViewport, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_Firecheckout/shipping-address/compact-list-button',
            visible: addressList().length > 3
        },
        expanded: ko.observable(false),
        active: ko.observable(false),

        /**
         * Initialize component
         */
        initialize: function () {
            this._super();

            if (this.visible) {
                $.async('#checkout-step-shipping .addresses', function (destination) {
                    $.async('.fc-compact-address-button', function (source) {
                        $(destination).before(source);
                    });
                });

                quote.shippingAddress.subscribe(function (address) {
                    var button = $('.fc-compact-address-button');

                    if (address.customerAddressId > 0 || address.saveInAddressBook) {
                        this.active(true);
                    } else {
                        this.active(false);
                    }

                    if (this.expanded()) {
                        this.expanded(false);

                        if (!isInViewport(button.get(0))) {
                            $('html, body').animate({
                                scrollTop: button.offset().top - 20
                            }, 300);
                        }
                    }
                }, this);
            }
        },

        /**
         * Prepare button title
         */
        initObservable: function () {
            this._super();

            this.title = ko.computed(function () {
                return this.expanded() ? $t('Hide Addresses') : $t('Show All Addresses');
            }, this);

            return this;
        },

        /**
         * Show/Hide addresses list
         */
        toggle: function () {
            if (this.expanded()) {
                this.expanded(false);
            } else {
                this.expanded(true);
            }
        }
    });
});
