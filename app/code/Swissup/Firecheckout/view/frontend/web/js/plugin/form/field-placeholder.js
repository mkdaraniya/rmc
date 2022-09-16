define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'ko',
    'Swissup_Firecheckout/js/utils/form-field/placeholder',
    'mage/translate'
], function (_, $, ko, fieldPlaceholder) {
    'use strict';

    /**
     * Add placeholder attribute to the form fields
     */
    return {
        /**
         * @param {Array} containers
         */
        init: function (containers) {
            var self = this,
                selector = [],
                fields = [
                    'input',
                    'select',
                    'textarea'
                ];

            _.each(containers, function (container) {
                _.each(fields, function (field) {
                    selector.push(container + ' ' + field);
                });
            });

            $.async({
                selector: selector.join(','),
                ctx: $('.page-title-wrapper').parent().get(0) || $('.checkout-container').get(0)
            }, function (el) {
                _.defer(self.handle.bind(self), $(el));
            });
        },

        /**
         * @param {jQuery} el
         */
        handle: function (el) {
            if (!el || el.get(0).type === 'hidden') {
                return;
            }

            if (el.attr('placeholder') && el.get(0).name !== 'password') {
                // password has a placeholder="optional" and it's not acceptible
                // when labels are hidden
                return;
            }

            fieldPlaceholder(el.get(0), this.getPlaceholderText(el));

            // fill empty option with label text,
            // otherwise input will be empty and without label
            if (el.get(0).tagName.toLowerCase() === 'select') {
                el.find('option[value=""]').each(function () {
                    if (!$(this).text().trim()) {
                        $(this).text($.mage.__('Please Select'));
                    }
                });
            }
        },

        /**
         * @param  {jQuery} el
         * @return {String}
         */
        getPlaceholderText: function (el) {
            var inputBox = this.getInputBox(el),
                text;

            if (!inputBox) {
                return '';
            }

            text = inputBox.find('> .label').text().trim();

            if (!text) {
                return;
            }

            if (inputBox.hasClass('_required')) {
                text += ' *';
            }

            return text;
        },

        /**
         * @param  {jQuery} el
         * @return {jQuery}
         */
        getInputBox: function (el) {
            return el.closest('.field');
        }
    };
});
