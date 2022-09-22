define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    return {
        /**
         * Move auth button to the page title row
         */
        init: function () {
            $.async('.authentication-wrapper', function (el) {
                var title = $('.page-title-wrapper:visible');

                if (title.length && title.width() > 50 && title.height() > 10) {
                    $('.page-title-wrapper').append(el);
                }
            });
        }
    };
});
