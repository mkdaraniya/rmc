define([], function () {
    'use strict';

    return {
        /**
         * Get current firecheckout layout mode
         * @returns {String}
         */
        getLayout: function () {
            return window.checkoutConfig.swissup.firecheckout.layout;
        },

        /**
         * Checks if current layout is a onecolumn expanded layout
         * @returns {Boolean}
         */
        isOneColumnExpanded: function () {
            return this.getLayout() === 'firecheckout-col1-set expanded';
        },

        /**
         * Checks if current layout is a multistep layout
         * @return {Boolean}
         */
        isMultistep: function () {
            return this.getLayout() === 'firecheckout-col1-set';
        },

        /**
         * Checks if email field moved to separate step
         * @return {Boolean}
         */
        isEmailOnSeparateStep: function () {
            return window.checkoutConfig.swissup.firecheckout.emailOnSeparateStep;
        },

        /**
         * @return {Boolean}
         */
        isBillingBeforeShipping: function () {
            return window.checkoutConfig.swissup.firecheckout.payment.billingBeforeShipping;
        }
    };
});
