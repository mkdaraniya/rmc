define([
    'Magento_Ui/js/lib/view/utils/async',
    './attr',
    './classname',
    './label',
    './mask',
    './placeholder',
    './validator',
    './newline',
    './status'
], function (
    $,
    attr,
    classname,
    label,
    mask,
    placeholder,
    validator,
    newline,
    status
) {
    'use strict';

    var mapping = {
        attr: attr,
        classname: classname,
        class: classname,
        label: label,
        mask: mask,
        placeholder: placeholder,
        validator: validator,
        newline: newline,
        status: status
    };

    /**
     * @param {Element} el
     * @param {Object} settings
     */
    function apply(el, settings) {
        $.each(settings, function (key, value) {
            if (!mapping[key]) {
                return console.error('[FormFieldManager]: ' + key + ' directive is not supported');
            }
            mapping[key](el, value);
        });
    }

    /**
     * @param {String|Element|Object} selector
     * @param {Object} settings
     */
    return function (selector, settings) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, settings);
            });
        } else {
            apply(selector, settings);
        }
    };
});
