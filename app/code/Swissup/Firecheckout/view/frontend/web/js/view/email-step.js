define([
    'Magento_Ui/js/lib/view/utils/async',
    'ko',
    'underscore',
    'uiComponent',
    'mage/translate',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/step-navigator'
], function ($, ko, _, Component, $t, customer, stepNavigator) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Swissup_Firecheckout/email-step',
            loginFormSelector: 'form[data-role=email-with-possible-login]'
        },

        isVisible: ko.observable(!customer.isLoggedIn()),

        /** [initialize description] */
        initialize: function () {
            this._super();

            if (customer.isLoggedIn()) {
                return this;
            }

            stepNavigator.registerStep(
                'email-address',
                null,
                $t('Email'),
                this.isVisible,
                _.bind(this.navigate, this),
                1
            );

            return this;
        },

        /** [navigate description] */
        navigate: function () {
            this.isVisible(true);
        },

        /** [navigateToNextStep description] */
        navigateToNextStep: function () {
            if (this.validateStep()) {
                stepNavigator.next();
            }
        },

        /** [validateStep description] */
        validateStep: function () {
            var result = {
                isValid: false
            };

            $(this.loginFormSelector).validation();

            result.isValid = Boolean($(this.loginFormSelector).find('input[name=username]').valid());

            $(document).trigger('fc:validate-email-step', result);

            return result.isValid;
        }
    });
});
