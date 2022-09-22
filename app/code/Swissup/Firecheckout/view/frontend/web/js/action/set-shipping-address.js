define([
    'Swissup_Firecheckout/js/model/shipping-address/save-processor'
], function (shippingAddressSaveProcessor) {
    'use strict';

    return function () {
        return shippingAddressSaveProcessor.saveShippingAddress();
    };
});
