define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

console.log("calll");
 $(document).on('keyup', 'input[name="account_number"]', function(e) {
var acc = $('input[name="account_number"]').val();
            document.cookie='account_number='+acc; 
        });
console.log("tested");
    $.widget('mage.checkoutAutocomplete', {
        initContainer: function () {
            $('#mp-delivery-comment').on('keyup', this.keyUpHandler)
        },

        keyUpHandler: function (e) { console.log('due to comment');
            if (e.target.getAttribute('type') === 'text') {
console.log('static hi');
                // Do something with event target
            }
        }
    });

    return $.mage.checkoutAutocomplete;
});
