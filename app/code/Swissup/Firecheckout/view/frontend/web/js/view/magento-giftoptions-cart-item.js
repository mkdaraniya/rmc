define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/totals'
], function ($, ko, Component, totals) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_FirecheckoutIntegrations/magento-giftoptions-cart-item'
        },
        totals: totals.totals(),
        items: ko.observable([]),
        maxCartItemsToDisplay: 30,
        cartUrl: window.checkoutConfig.cartUrl,
        isVisible: ko.observable(false),

        /**
         * [initialize description]
         */
        initialize: function () {
            var self = this;

            this._super();

            // Set initial items to observable field
            this.setItems(totals.getItems()());

            $.async('.gift-options-cart-item > .action-gift', function (giftOptionsItem) {
                var optionsContext = ko.contextFor(giftOptionsItem);

                self.isVisible(true);

                $.async(
                    '.fc-magento-giftoptions-cart-item .minicart-items > .product-item .product',
                    function (minicartItem) {
                        var minicartContext = ko.contextFor(minicartItem);

                        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                        if (minicartContext.$data.item_id == optionsContext.$data.itemId) {
                            $(giftOptionsItem).parent().appendTo(minicartItem);
                        }
                        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    }
                );
            });
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items) {
            if (items && items.length > 0) {
                items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
            }
            this.items(items);
        },

        /**
         * Returns count of cart line items
         *
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            return parseInt(totals.getItems()().length, 10);
        }
    });
});
