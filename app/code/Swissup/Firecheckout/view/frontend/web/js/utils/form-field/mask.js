define([
    'Magento_Ui/js/lib/view/utils/async',
    'Swissup_Firecheckout/js/lib/vanillaTextMask',
    './validator',
    'mage/translate'
], function ($, vanillaTextMask, validator, $t) {
    'use strict';

    var mapping = {
            'number': 'tel',
            'email': 'text'
        },
        masks = {};

    /**
     * Checks if string is starts with one of the values
     *
     * @param  {String} string
     * @param  {String|RegExp|Array} values
     * @return {Boolean}
     */
    function startsWith(string, values) {
        if (typeof values !== 'object' || !values.length) {
            values = [values];
        }

        return -1 !== values.findIndex(function (value) {
            if (value instanceof RegExp) {
                return value.test(string);
            }

            return string.indexOf(value) === 0;
        });
    }

    masks = {
        phone: {
            guide: false,

            /**
             * @return {Array}
             */
            mask: function () {
                return [
                    '(', /\d/, /\d/, /\d/, ')', // (096)
                    ' ',
                    /\d/, /\d/, /\d/,           // 555
                    '-',
                    /\d/, /\d/, /\d/, /\d/      // 5555
                ];
            },

            validator: {
                lazy: true,
                'fc-custom-rule-phone': {
                    /**
                     * @param  {String} value
                     * @return {Boolean}
                     */
                    handler: function (value) {
                        if (!value.length) {
                            return true;
                        }

                        return new RegExp(/^\(\d{3}\) \d{3}\-\d{4}$/).test(value);
                    },
                    message: $t('Please use the following format: (555) 555-5555')
                }
            }
        },
        cc: {
            guide: false,

            /**
             * @param  {String} raw
             * @return {Array}
             */
            mask: function (raw) {
                var mask = [
                    /\d/, /\d/, /\d/, /\d/, // 4111
                    ' ',
                    /\d/, /\d/, /\d/, /\d/, // 1111
                    ' ',
                    /\d/, /\d/, /\d/, /\d/, // 1111
                    ' ',
                    /\d/, /\d/, /\d/, /\d/  // 1111
                ];

                if (startsWith(raw, [/30[0-5]/, '309', '34', '37'])) {
                    mask = [
                        /\d/, /\d/, /\d/, /\d/,             // 3050
                        ' ',
                        /\d/, /\d/, /\d/, /\d/, /\d/, /\d/, // 111111
                        ' ',
                        /\d/, /\d/, /\d/, /\d/              // 1111
                    ];
                }

                return mask;
            }
        }
    };

    /**
     * @param {Element} el
     * @param {String} type
     * @param {Object} settings
     */
    function apply(el, type, settings) {
        settings = settings || {};

        if (typeof type === 'string') {
            type = masks[type];
        }
        settings = $.extend({}, type, settings);

        if (mapping[el.type]) {
            el.type = mapping[el.type];
        }

        settings.inputElement = el;

        if (settings.validator) {
            validator(el, settings.validator);
            delete settings.validator;
        }

        $(el).data('fc-mask', vanillaTextMask.maskInput(settings));
    }

    /**
     * @param  {String} selector
     * @param  {String|Object} type
     * @param  {Object} settings
     */
    return function (selector, type, settings) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, type, settings);
            });
        } else {
            apply(selector, type, settings);
        }
    };
});
