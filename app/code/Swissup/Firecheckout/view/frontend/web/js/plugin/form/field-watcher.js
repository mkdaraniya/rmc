define([
    'underscore',
    'Magento_Ui/js/lib/view/utils/async'
], function (_, $) {
    'use strict';

    /**
     * Add `fc-dirty` class name to all non-empty fields
     */
    return {
        /**
         * Initialize plugin
         */
        init: function () {
            var self = this;

            this.addObservers();

            $.async({
                selector: 'input, select, textarea',
                ctx: $('.page-title-wrapper').parent().get(0) || $('.checkout-container').get(0)
            }, function (el) {
                _.defer(self.handle.bind(self), $(el));
            });
        },

        /**
         * Add field observers
         */
        addObservers: function () {
            var self = this,
                events = [
                    'change',
                    'keyup',
                    'keydown',
                    'paste',
                    'cut'
                ];

            $(document.body).on(events.join(' '), 'input, select, textarea', function (e) {
                // delay is used to fix paste and cut events
                _.delay(self.handle.bind(self), 120, $(e.target));
            });
        },

        /**
         * @param  {jQuery} el
         */
        handle: function (el) {
            var inputBox;

            if (!el || el.get(0).type === 'hidden' || !el.val) {
                return;
            }

            inputBox = this.getInputBox(el);

            if (inputBox) {
                inputBox.toggleClass('fc-dirty', this.isDirty(el));
            }
        },

        /**
         * @param  {jQuery}  el
         * @return {Boolean}
         */
        isDirty: function (el) {
            var value = el.val(),
                dirty = !!value,
                domEl = el.get(0);

            if (domEl.tagName.toLowerCase() === 'select') {
                dirty = el.find(':selected').text().trim().length > 0;
            }

            return dirty;
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
