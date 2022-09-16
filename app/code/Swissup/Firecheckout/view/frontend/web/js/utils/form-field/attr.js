define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    /**
     * @param {Element} el
     * @param {Object} attributes
     */
    function apply(el, attributes) {
        $.each(attributes, function (key, value) {
            if (key.indexOf('data-') === 0) {
                $(el).data(key.replace('data-', ''), value);
            } else {
                $(el).attr(key, value);
            }
        });
    }

    /**
     * @param {String|Element} selector
     * @param {Object} attributes
     */
    return function (selector, attributes) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, attributes);
            });
        } else {
            apply(selector, attributes);
        }
    };
});
