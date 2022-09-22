define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async',
    'Swissup_Firecheckout/js/utils/form-field/label',
    'mage/translate'
], function (_, $, fieldLabel) {
    'use strict';

    /**
     * Add labels to grouped inputs (street lines, etc)
     */
    return {
        /**
         * Plugin initialization
         */
        init: function () {
            var self = this;

            $.async({
                selector: 'fieldset.field',
                ctx: $('.page-title-wrapper').parent().get(0) || $('.checkout-container').get(0)
            }, function (el) {
                _.defer(self.handle.bind(self), $(el));
            });
        },

        /**
         * @param  {jQuery} el
         */
        handle: function (el) {
            var label,
                firstLabelText,
                labelText;

            if (!el) {
                return;
            }

            firstLabelText = el.find('.label span').first().text().trim();

            if (!firstLabelText) {
                return;
            }

            el.find('.field').each(function (i) {
                label = $(this).find('.label').first();
                labelText = firstLabelText;

                if (!label || label.text().trim()) {
                    return;
                }

                if (i > 0) {
                    labelText = labelText + ' ' + (i + 1);
                }

                fieldLabel(label, labelText);
            });
        }
    };
});
