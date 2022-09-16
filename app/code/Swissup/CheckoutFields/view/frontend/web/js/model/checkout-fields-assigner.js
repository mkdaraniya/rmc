define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    'use strict';

    var swissupCheckoutFieldsEnabled = window.checkoutConfig.swissupCheckoutFieldsEnabled;

    /** Override default place order action and add checkout fields to request */
    return function (paymentData) {
        if (!swissupCheckoutFieldsEnabled) {
            return;
        }

        var checkoutFields = $('.field[name*="swissupCheckoutFields"]'),
            checkoutProvider = registry.get('checkoutProvider'),
            checkoutFieldsData = {};

        checkoutFields.each(function(index, item) {
            var itemName = item.name;
            var fieldCode = itemName.slice(itemName.indexOf('[') + 1, itemName.indexOf(']'));
            checkoutFieldsData[fieldCode] = checkoutProvider.get(itemName);
        });

        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }

        paymentData['extension_attributes']['swissup_checkout_fields'] = checkoutFieldsData;
    };
});
