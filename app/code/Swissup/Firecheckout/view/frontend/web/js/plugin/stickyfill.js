define([
    'Magento_Ui/js/lib/view/utils/async',
    'stickyfill'
], function ($, Stickyfill) {
    'use strict';

    return {
        /**
         * Plugin initialization
         */
        init: function () {
            $.async('.opc-sidebar', function (el) {
                Stickyfill.add(el);
            });
        }
    };
});
