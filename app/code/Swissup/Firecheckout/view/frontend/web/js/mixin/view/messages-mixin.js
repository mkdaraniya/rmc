define([
    'jquery'
], function ($) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * @param {Boolean} isHidden
             */
            onHiddenChange: function (isHidden) {
                var self = this;

                // Hide message block if needed
                if (isHidden) {
                    // don't hide message too early
                    if (self.fcTimeout) {
                        clearTimeout(self.fcTimeout);
                    }

                    self.fcTimeout = setTimeout(function () {
                        $(self.selector).hide('blind', {}, 500);
                    }, 8000);
                }
            }
        });
    };
});
