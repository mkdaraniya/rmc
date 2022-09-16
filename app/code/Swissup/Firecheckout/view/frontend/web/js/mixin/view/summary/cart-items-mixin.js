define([], function () {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /**
             * Always show cart items
             * @returns {*|Boolean}
             */
            isItemsBlockExpanded: function () {
                // call parent because payment are not visible if not called
                this._super();

                return true;
            }
        });
    };
});
