define([
    'Swissup_Firecheckout/js/model/shipping-method/save-processor'
], function (shippingMethodSaveProcessor) {
    'use strict';

    return function () {
        return shippingMethodSaveProcessor.saveShippingMethod();
    };
});
