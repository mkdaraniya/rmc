define([
    'jquery',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Swissup_Firecheckout/js/model/layout',
    'Swissup_Firecheckout/js/model/validator',
    'Swissup_Firecheckout/js/action/sync-billing-address',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/payment-service'
], function (
    $,
    _,
    quote,
    layout,
    validator,
    syncBillingAddress,
    setShippingInformationAction,
    paymentService
) {
    'use strict';

    /**
     * @param  {Object} result
     * @return {Object}
     */
    function submitShippingInformationCallback(result) {
        $('body').trigger($.Event('fc:placeOrderSetShippingInformationAfter', {
            response: result
        }));

        delete paymentService.doNotUpdate;

        return result;
    }

    return {
        /**
         * Place Order method
         */
        placeOrder: _.debounce(function () {
            var event;

            syncBillingAddress();

            quote.firecheckout.state.placeOrderPressed = true;

            if (!validator.validate()) {
                quote.firecheckout.state.placeOrderPressed = false;

                return false;
            }

            event = $.Event('fc:placeOrderBefore', {
                cancel: false
            });
            $('body').trigger(event);

            // allow to interrupt the process
            if (event.cancel) {
                quote.firecheckout.state.placeOrderPressed = false;

                return;
            }

            if (layout.isMultistep()) {
                this._place();
            } else {
                $.when(this.submitShippingInformation()).done(this._place);
            }
        }, 200),

        /**
         * Click hidden "Place Order" button in payment section
         */
        _place: function () {
            quote.firecheckout.state.placeOrderPressed = false;

            $(
                [
                    '.actions-toolbar:not([style="display: none;"])',
                    '.action.checkout:not([style="display: none;"])'
                ].join(' '),
                '.payment-method._active'
            ).trigger('click');

            // try to call button click method directly, without .click() emulation

            $('body').trigger('fc:placeOrderAfter');
        },

        /**
         * @return {Deferred|Boolean}
         */
        submitShippingInformation: function () {
            if (!quote.isVirtual() && quote.shippingMethod()) {
                paymentService.doNotUpdate = true;

                $('body').trigger($.Event('fc:placeOrderSetShippingInformationBefore'));

                return setShippingInformationAction()
                    .then(
                        submitShippingInformationCallback,
                        submitShippingInformationCallback
                    );
            }

            return true;
        }
    };
});
