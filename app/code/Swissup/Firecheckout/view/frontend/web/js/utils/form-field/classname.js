define([
    'Magento_Ui/js/lib/view/utils/async'
], function ($) {
    'use strict';

    /**
     * @param {Element} el
     * @param {String|Object} classname
     * @param {Boolean} flag
     */
    function apply(el, classname, flag) {
        var classnames = classname;

        el = $(el).closest('.control').closest('.field');

        if (typeof classnames === 'string') {
            classnames = {};
            classnames[classname] = flag;
        }

        $.each(classnames, function (_classname, _flag) {
            if (_flag === false) {
                $(el).removeClass(_classname);
            } else {
                $(el).addClass(_classname);
            }
        });
    }

    /**
     * @param {String|Element} selector
     * @param {String|Object} classname
     * @param {Boolean} flag
     */
    return function (selector, classname, flag) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, classname, flag);
            });
        } else {
            apply(selector, classname, flag);
        }
    };
});
