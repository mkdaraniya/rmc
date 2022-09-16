define([
    'underscore',
    'Magento_Checkout/js/model/step-navigator',
    'mage/translate'
], function (_, stepNavigator, $t) {
    'use strict';

    return {
        /**
         * Add cart item into progress bar
         */
        init: function () {
            stepNavigator.registerStep(
                'cart',
                null,
                $t('Cart'),
                this.isVisible,
                _.bind(this.navigate, this),
                -99
            );
        },

        /**
         * This step is always invisible
         * @returns {Boolean}
         */
        isVisible: function () {
            return false;
        },

        /**
         * Handle navigate to cart step
         */
        navigate: function () {
            window.location = window.checkoutConfig.cartUrl;
        }
    };
});
