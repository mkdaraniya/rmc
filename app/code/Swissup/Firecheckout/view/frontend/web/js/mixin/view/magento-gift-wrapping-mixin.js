define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_GiftMessage/js/model/gift-options'
], function ($, wrapper, giftOptions) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        // copy from module-gift-wrapping/view/frontend/web/js/model/gift-wrapping.js
        giftWrappingConfig = window.giftOptionsConfig ?
            window.giftOptionsConfig.giftWrapping :
            window.checkoutConfig.giftWrapping;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * Initialize mixin
             */
            initialize: function () {
                var self = this;

                this._super();

                // Since `module-gift-wrapping/view/frontend/web/js/model/gift-wrapping.js`
                // is fully private, we can change its methods on a created instance only.
                // At this point "this.model" is created. We can disable page reload now.
                this.model.isExtraOptionsApplied = wrapper.wrap(
                    this.model.isExtraOptionsApplied,
                    function (originalMethod) {
                        // fix to reflect customer's selection without page reload
                        var appliedGiftReceipt = this.getObservable('wrapping-' + this.itemId, 'giftReceipt')(),
                            appliedPrintedCard = this.getObservable('wrapping-' + this.itemId, 'printedCard')();

                        // These variables are checked in originalMethod
                        giftWrappingConfig.appliedGiftReceipt = appliedGiftReceipt;
                        giftWrappingConfig.appliedPrintedCard = appliedPrintedCard;

                        return originalMethod() || this.getObservable('activeWrapping', this.itemId)();
                    }
                );

                this.model.getAppliedWrappingId = wrapper.wrap(
                    this.model.getAppliedWrappingId,
                    function (originalMethod) {
                        return originalMethod() || this.getObservable('activeWrapping', this.itemId)();
                    }
                );

                this.model.getSubmitParams = wrapper.wrap(
                    this.model.getSubmitParams,
                    function (originalMethod, remove) {
                        if (remove) {
                            this.getObservable('wrapping-' + this.itemId, 'giftReceipt')(null);
                            this.getObservable('wrapping-' + this.itemId, 'printedCard')(null);

                            if (this.getObservable('activeWrapping', this.itemId)()) {
                                self.uncheckWrapping();
                            }
                        }

                        return originalMethod.apply(
                            target,
                            Array.prototype.slice.call(arguments, 1)
                        );
                    }
                );

                /**
                 * Fix to show block when gift message is not set, but options are.
                 */
                giftOptions.options.subscribe(function () {
                    this.updateAdditionalOptionsApplied();
                }, this, 'arrayChange');
            },

            /**
             * Fix to show block when gift message is not set, but options are.
             */
            applyWrapping: function () {
                this.updateAdditionalOptionsApplied();
                this._super();
            },

            /**
             * Update flag in module-gift-message/view/frontend/web/js/model/gift-message.js
             */
            updateAdditionalOptionsApplied: function () {
                var giftMessage = giftOptions.getOptionByItemId(this.levelIdentifier);

                if (giftMessage) {
                    giftMessage.getObservable('additionalOptionsApplied')(this.isExtraOptionsApplied());

                    if (!giftMessage.getObservable('alreadyAdded')() &&
                        this.isExtraOptionsApplied()) {

                        // show options on initial page load when giftwrap is selected only
                        giftMessage.getObservable('alreadyAdded')(true);
                    }
                }
            }
        });
    };
});
