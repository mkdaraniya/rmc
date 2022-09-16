define([
    'Magento_Ui/js/lib/view/utils/async',
    'Swissup_Firecheckout/js/utils/move'
], function ($, move) {
    'use strict';

    return {
        /**
         * Initialize Plugin
         */
        init: function (settings) {
            $.async('.opc-block-shipping-information .shipping-information', function () {
                if (settings.title) {
                    $('.opc-block-shipping-information').prepend(
                        '<div class="fc-heading">' + settings.title + '</div>'
                    );
                }
                move('.opc-block-shipping-information').after('.opc-block-summary > .title', 100);
            });
        }
    };
});
