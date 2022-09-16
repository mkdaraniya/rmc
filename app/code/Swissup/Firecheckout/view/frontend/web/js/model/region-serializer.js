define([
    'jquery'
], function ($) {
    'use strict';

    return {

        /**
         * Collect all values from all inputs inside from parent element
         *
         * @param  {jQuery} region
         * @return {Object}
         */
        serialize: function (region) {
            var data = {},
                iterators = {};

            region.find('input, select').each(function (i, input) {
                var key;

                if (input.id && input.id.length && input.id.indexOf(' ') === -1) {
                    key = '[id="' + input.id + '"]';
                } else if (input.name && input.name.length) {
                    key = '[name="' + input.name + '"]';
                } else {
                    return;
                }

                if (typeof iterators[key] === 'undefined') {
                    iterators[key] = 0;
                }
                iterators[key]++;

                if ($(key).length > 1) {
                    // in case if elements has the same name
                    key += ':nth-child(' + iterators[key] + ')';
                }

                if (input.type === 'radio' || input.type === 'checkbox') {
                    data[key] = $(this).prop('checked');
                } else {
                    data[key] = $(this).val();
                }
            });

            return data;
        },

        /**
         * Fill inputs of the region with
         *
         * @param {jQuery} region
         * @param {Object} data
         */
        restore: function (region, data) {
            $.each(data, function (selector, value) {
                region.find(selector).each(function (i, input) {
                    if (input.type === 'radio' || input.type === 'checkbox') {
                        $(this).prop('checked', value);
                    } else {
                        $(this).val(value);
                    }
                });
            });
        }
    };
});
