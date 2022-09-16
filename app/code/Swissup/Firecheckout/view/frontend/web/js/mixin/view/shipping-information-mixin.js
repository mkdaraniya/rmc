define([
    'Magento_Checkout/js/model/quote'
], function (quote) {
    'use strict';

    return function (target) {
        return target.extend({
            /**
             * @return {Boolean}
             */
            isVisible: function () {
                return !quote.isVirtual();
            }
        });
    };
});
