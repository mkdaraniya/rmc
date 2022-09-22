define([
    'Magento_Ui/js/lib/view/utils/async',
    './validator',
    './classname'
], function ($, validator, classname) {
    'use strict';

    /**
     * @param {Element} el
     */
    function optional(el) {
        classname(el, 'fc-hidden', false);
        validator(el, false);
    }

    /**
     * @param {Element} el
     */
    function hidden(el) {
        optional(el);
        classname(el, 'fc-hidden');
    }

    /**
     * @param {Element} el
     */
    function required(el) {
        optional(el);
        validator(el);
    }

    /**
     * @param {Element} el
     * @param {String} status
     */
    function apply(el, status) {
        var mapping = {
            hidden: hidden,
            optional: optional,
            required: required
        };

        if (!mapping[status]) {
            return console.error('Unknown field status: ' + status);
        }

        mapping[status](el);
    }

    /**
     * @param {String|Element} selector
     * @param {String} status
     */
    return function (selector, status) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, status);
            });
        } else {
            apply(selector, status);
        }
    };
});
