define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    return {
        /**
         * Initialize plugin
         */
        init: function () {
            $.async('.step-title, .shipping-information-title span', function (el) {
                var html = el.innerHTML;

                if (html.substr(-1) === ':') {
                    el.innerHTML = html.substr(0, html.length - 1);
                }
            });
        }
    };
});
