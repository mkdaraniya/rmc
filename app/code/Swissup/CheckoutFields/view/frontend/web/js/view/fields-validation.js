define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Swissup_CheckoutFields/js/model/fields-validator'
    ],
    function (Component, additionalValidators, fieldsValidator) {
        'use strict';
        additionalValidators.registerValidator(fieldsValidator);
        return Component.extend({});
    }
);
