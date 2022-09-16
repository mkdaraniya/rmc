define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * Use popup unblocker if button that will trigger braintree popup is visible.
         *
         * @return {Boolean}
         */
        canUse: function () {
            var toolbar = $('#braintree_paypal_continue_to, #braintree_paypal_pay_with')
                .closest('.actions-toolbar');

            return toolbar.is(':visible');
        }
    };
});
