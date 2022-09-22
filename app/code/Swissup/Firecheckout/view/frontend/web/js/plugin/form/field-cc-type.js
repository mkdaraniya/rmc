define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    return {
        /**
         * Initialize the plugin
         */
        init: function () {
            $.async('.ccard .field.type', function (el) {
                if ($(el).find('> label').length || !$(el).find('img').length) {
                    return;
                }

                $(el).siblings('.field.number').first().find('.control:visible').first().append(el);
            });
        }
    };
});
