define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    /**
     * @param {Element} el
     * @param {Boolean} flag
     * @param {Object} css
     */
    function apply(el, flag, css) {
        var spinner;

        if (flag) {
            spinner = $('<div class="fc-spinner"></div>').css(css || {});

            $(el).after(spinner);

            // use timeout to enable css animation delay
            setTimeout(function () {
                spinner.addClass('shown');
            }, 10);
        } else {
            $(el).siblings('.fc-spinner').remove();
        }
    }

    /**
     * @param {String|Element} selector
     * @param {Boolean} flag
     * @param {Object} css
     */
    return function (selector, flag, css) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, flag, css);
            });
        } else {
            apply(selector, flag, css);
        }
    };
});
