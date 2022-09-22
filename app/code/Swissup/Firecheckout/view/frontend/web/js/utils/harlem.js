define([
    'jquery'
], function ($) {
    'use strict';

    return {
        /**
         * Con los terroristas
         * Do the Harlem Shake
         *
         * @param {Element|jQuery} el
         */
        shake: function (el) {
            $(el).addClass('firecheckout-shake')
                .one(
                    'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend',
                    function () {
                        $(this).removeClass('firecheckout-shake');
                    }
                );
        }
    };
});
