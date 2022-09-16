define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'Swissup_Firecheckout/js/lib/ResizeSensor'
], function ($, _, ResizeSensor) {
    'use strict';

    var rules = {
        'fc-size-l': 550,
        'fc-size-m': 420,
        'fc-size-s': 275,
        'fc-size-xs': 0
    };

    return {
        /**
         * @param {String} selector
         */
        init: function (selector) {
            var self = this;

            $.async({
                selector: selector,
                ctx: $('body').get(0)
            }, function (el) {
                new ResizeSensor(el, self.handle.bind(self, el));
            });
        },

        /**
         * Add CSS class names when container size matches rule
         *
         * @param {Element} el
         */
        handle: function (el) {
            var $el = $(el),
                match = false;

            _.each(rules, function (minWidth, className) {
                $el.removeClass(className);

                if (!match && $el.width() > minWidth) {
                    match = true;
                    $el.addClass(className);
                }
            });
        }
    };
});
