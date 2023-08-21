define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
'domReady!'
], function ($, ko, Component, quote) {
    'use strict';
            document.cookie='account_number=1234'; 
jQuery('#acc').keyup(function(e){console.log('test2');});
console.log("test required");

jQuery('#acc').prop('required',true);
jQuery('#acc').attr('required',true);
jQuery("#co-shipping-method-form").removeAttr("novalidate");

$('#acc').prop('required',true);
    return Component.extend({
        defaults: {
            template: 'Scrumwheel_Shippy/checkout/shipping/carrier_custom'
        },

        initObservable: function () {
        this._super();
console.log("default call");
            this.selectedMethod = ko.computed(function() {
console.log("call test");
jQuery("#co-shipping-method-form").removeAttr("novalidate");
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);

            return this;
        },
    });
});
