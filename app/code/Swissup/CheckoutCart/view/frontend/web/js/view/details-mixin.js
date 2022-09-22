define([
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'Magento_Checkout/js/model/quote',
    'Swissup_CheckoutCart/js/action/update-cart',
    'Swissup_CheckoutCart/js/action/remove-item'
], function ($, urlBuilder, $t, modalConfirm, quote, updateCartAction, removeItemAction) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        config = {},
        memo = {};

    return function (target) {
        if (!checkoutConfig ||
            !checkoutConfig.swissup ||
            !checkoutConfig.swissup.CheckoutCart ||
            !checkoutConfig.swissup.CheckoutCart.enabled
        ) {
            return target;
        }

        config = checkoutConfig.swissup.CheckoutCart;

        //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
        return target.extend({
            /**
             * @param {int} id
             * @return {Boolean}
             */
            qtyIncrementEnabled: function (id) {
                return this.getQuoteItemById(id) && config.qtyIncrementEnabled;
            },

            /**
             * @param {Object} item
             */
            incQty: function (item) {
                this.applyQty(
                    item, item.qty * 1 + this.getQtyStep(item)
                );
            },

            /**
             * @param  {Object} item
             */
            decQty: function (item) {
                var qtyStep = this.getQtyStep(item);

                if (item.qty - qtyStep <= 0) {
                    this.removeItem(item);
                } else {
                    this.applyQty(item, item.qty - qtyStep);
                }
            },

            /**
             * @param  {Object} item
             * @return {Number}
             */
            getQtyStep: function (item) {
                var quoteItem = this.getQuoteItemById(item.item_id);

                return quoteItem && quoteItem.qty_increments ? quoteItem.qty_increments : 1;
            },

            /**
             * @param  {Object} item
             * @param  {Object} event
             */
            newQty: function (item, event) {
                var quoteItem = this.getQuoteItemById(item.item_id);

                if (item.qty == 0) { // eslint-disable-line eqeqeq
                    this.removeItem(item, event);
                } else if (this.isValidQty(quoteItem.qty, item.qty)) {
                    this.applyQty(item, item.qty, $(event.target));
                } else {
                    item.qty = quoteItem.qty;
                    $(event.target).val(item.qty);
                }
            },

            /**
             * @param {Object} item
             * @param {Number} qty
             * @param {Object} input
             */
            applyQty: function (item, qty, input) {
                var quoteItem = this.getQuoteItemById(item.item_id),
                    params = {
                        cartItem: {
                            item_id: item.item_id,
                            qty: qty,
                            quote_id: quote.getQuoteId()
                        }
                    },
                    oldQty = quoteItem.qty,
                    input = input || null;

                quoteItem.qty = qty;
                updateCartAction(
                    quote, params
                ).fail(function(response) {
                    item.qty = oldQty;
                    quoteItem.qty = oldQty;
                    if (input) input.val(oldQty);
                });
            },

            /**
             * @param {Object} item
             * @param {Object} event
             */
            removeItem: function (item, event) {
                var quoteItem = this.getQuoteItemById(item.item_id);

                modalConfirm({
                    content: $t('Are you sure you want to remove this item?'),
                    actions: {
                        /**
                         * Remove item from cart
                         */
                        confirm: function () {
                            removeItemAction(quote, item.item_id);
                        },

                        /**
                         * Cancel action
                         */
                        cancel: function () {
                            if (event) {
                                item.qty = quoteItem.qty;
                                $(event.target).val(item.qty);
                            }
                        }
                    }
                });
            },

            /**
             * @param  {Number} origin
             * @param  {Number} changed
             * @return {Boolean}
             */
            isValidQty: function (origin, changed) {
                return origin != changed && // eslint-disable-line eqeqeq
                    changed.length > 0 &&
                    changed - 0 == changed && // eslint-disable-line eqeqeq
                    changed - 0 > 0;
            },

            /**
             * @param  {Number} itemId
             * @return {Object}
             */
            getQuoteItemById: function (itemId) {
                if (memo[itemId]) {
                    return memo[itemId];
                }

                memo[itemId] = $.grep(quote.getItems(), function (item) {
                    return item.item_id == itemId; // eslint-disable-line eqeqeq
                })[0];

                return memo[itemId];
            },

            /**
             * @param  {Number} itemId
             * @return {Boolean}
             */
            productLinkEnabled: function (itemId) {
                var quoteItem = this.getQuoteItemById(itemId);

                return quoteItem && quoteItem.product.request_path && config.productLinkEnabled;
            },

            /**
             * @param  {Number} itemId
             * @return {String}
             */
            getProductHref: function (itemId) {
                var quoteItem = this.getQuoteItemById(itemId);

                return urlBuilder.build(quoteItem.product.request_path);
            },

            /**
             * @return {Boolean}
             */
            productSkuEnabled: function () {
                return config.productSkuEnabled;
            },

            /**
             * @return {String}
             */
            getProductSku: function (itemId) {
                var quoteItem = this.getQuoteItemById(itemId);

                return quoteItem ? $t('SKU') + ': ' + quoteItem.product.sku : '';
            }
        });
        //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
    };
});
