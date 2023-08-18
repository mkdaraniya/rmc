define([
    'jquery',
    'Swissup_Checkout/js/is-in-viewport',
    'Swissup_Firecheckout/js/action/set-shipping-method'
], function ($, isInViewport, setShippingMethod) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * Initialize.
             */
            initialize: function () {
                this._super();

                /**
                 * Since `module-gift-message/view/frontend/web/js/model/gift-message.js`
                 * is fully private, we can change its methods on a created instance only.
                 * At this point "this.model" is created. We can disable page reload now.
                 */
                this.model.afterSubmit = function () {
                    // need to save shipping method to get correct totals
                    setShippingMethod();
                };
            },

            /**
             * Is result block visible
             */
            isResultBlockVisible: function () {
                this._super();

                // fix to show selected giftwrap on initial page load
                if (this.model.getObservable('additionalOptionsApplied')()) {
                    this.resultBlockVisibility(true);
                }
            },

            /**
             * Hide form block
             */
            hideFormBlock: function () {
                this.scrollToTitle();
                this._super();
            },

            /**
             * Delete options
             */
            deleteOptions: function () {
                this._super();

                this.scrollToTitle();
                this.resultBlockVisibility(false);
                this.formBlockVisibility(false);

                this.model.getObservable('alreadyAdded')(false);
                this.model.getObservable('recipient')('');
                this.model.getObservable('sender')('');
                this.model.getObservable('message')('');
            },

            /**
             * Submit options
             */
            submitOptions: function () {
                this._super();

                this.scrollToTitle();
                this.resultBlockVisibility(true);
                this.formBlockVisibility(false);
                this.model.getObservable('alreadyAdded')(true);
            },

            /**
             * Scroll to GiftOptions title if it's not in the viewport
             */
            scrollToTitle: function () {
                var el = $('#gift-options-cart');

                if (!isInViewport(el.get(0))) {
                    $('html, body').scrollTop(el.offset().top - 20);
                }
            }
        });
    };
});
