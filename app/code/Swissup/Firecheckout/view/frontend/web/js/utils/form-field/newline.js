define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    /**
     * @param {Element} el
     * @param {Object} sizes
     */
    function apply(el, sizes) {
        var newline = $('<div class="fc-newline"></div>');

        el = $(el).closest('.field');

        if (!sizes || sizes === true) {
            sizes = [
                'fc-size-l',
                'fc-size-m',
                'fc-size-s',
                'fc-size-xs'
            ];
        } else if (typeof sizes === 'string') {
            sizes = sizes.split(' ');
        }

        $.each(sizes, function (i, size) {
            newline.addClass(size + ':fc-newline');
        });

        $(el).before(newline);
    }

    /**
     * @param {String|Element} selector
     * @param {Object} sizes
     */
    return function (selector, sizes) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, sizes);
            });
        } else {
            apply(selector, sizes);
        }
    };
});
