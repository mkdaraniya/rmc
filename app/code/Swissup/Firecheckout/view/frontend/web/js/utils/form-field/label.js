define([
    'Magento_Ui/js/lib/view/utils/async',
    './placeholder'
], function ($, placeholder) {
    'use strict';

    /**
     * @param {Element} el
     * @param {String} label
     * @param {Boolean} syncPlaceholderValue
     */
    function apply(el, label, syncPlaceholderValue) {
        var labelElement,
            wrapper;

        wrapper = $(el).closest('.field');

        if (!wrapper) {
            return;
        }

        labelElement = wrapper.find('.label').first();

        if (!labelElement) {
            wrapper.prepend('<label class="label"></label>');
            labelElement = wrapper.find('.label').first();
        }

        if (!labelElement.find('span').length) {
            labelElement.prepend('<span></span>');
        }

        labelElement.find('span').text(label);

        if (syncPlaceholderValue) {
            placeholder(el, label);
        }
    }

    /**
     * @param {String|Element} selector
     * @param {String} label
     * @param {Boolean} syncPlaceholderValue
     */
    return function (selector, label, syncPlaceholderValue) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, label, syncPlaceholderValue);
            });
        } else {
            apply(selector, label, syncPlaceholderValue);
        }
    };
});
