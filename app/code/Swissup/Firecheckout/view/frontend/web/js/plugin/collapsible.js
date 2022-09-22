define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'Swissup_Firecheckout/js/utils/collapsible'
], function ($, _, collapsible) {
    'use strict';

    var defaults = {
        header: '.payment-option-title',
        content: '.payment-option-content',
        openedState: '_active'
    };

    return {
        /**
         * @param {Object} settings
         */
        init: function (settings) {
            if (!settings) {
                return;
            }

            _.each(settings, function (config) {
                $.async(config.selector, function (el) {
                    // delay to invoke mageInit first
                    _.delay(collapsible, 100, el, $.extend({}, defaults, config));
                });
            });
        }
    };
});
