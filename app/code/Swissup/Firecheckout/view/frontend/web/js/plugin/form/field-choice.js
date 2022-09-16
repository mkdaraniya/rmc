define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    return {
        /**
         * Initialize the plugin
         */
        init: function () {
            $.async('.choice.field', function (el) {
                $(el).parent().closest('.field').addClass('fc-field-choice');
            });
        }
    };
});
