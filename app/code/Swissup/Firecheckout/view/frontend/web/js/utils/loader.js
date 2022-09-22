define([
    'jquery'
], function ($) {
    'use strict';

    var classname = '_block-content-loading',
        template = '<div class="loading-mask fc-loader"><div class="loader"></div></div>';

    return function (selector) {
        return {
            show: function () {
                if ($(selector).hasClass(classname)) {
                    return;
                }

                $(selector).addClass(classname).append(template);
            },

            hide: function () {
                $(selector)
                    .find('.fc-loader').remove().end()
                    .removeClass(classname);
            }
        }
    }
});
