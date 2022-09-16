define([
    'Magento_Ui/js/lib/view/utils/async',
    'ko'
], function ($, ko) {
    'use strict';

    /**
     * @param {Element} el
     * @param {String} placeholder
     */
    function apply(el, placeholder) {
        var koElement = ko.dataFor(el);

        if (koElement) {
            if (typeof koElement.placeholder === 'function') {
                koElement.placeholder(placeholder);
            } else {
                koElement.placeholder = placeholder;
            }
        }

        $(el).attr('placeholder', placeholder);
    }

    /**
     * @param {String|Element} selector
     * @param {String} placeholder
     */
    return function (selector, placeholder) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, placeholder);
            });
        } else {
            apply(selector, placeholder);
        }
    };
});
