define([], function () {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * Always show order totals
             * @returns {Boolean}
             */
            isFullMode: function () {
                // call parent because payment are not visible if not called
                this._super();

                if (!this.getTotals()) {
                    return false;
                }

                return true;
            }
        });
    };
});
