define([
    'jquery',
    'ko',
    'Swissup_Firecheckout/js/utils/form-field/spinner'
], function ($, ko, spinner) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            /** [initObservable description] */
            initObservable: function () {
                var css = {};

                if ($('body').hasClass('rtl')) {
                    css.left = 30;
                } else {
                    css.right = 30;
                }

                this._super();

                /** Disable loading spinner */
                this.isLoading = ko.pureComputed({
                    /** [read description] */
                    read: function () {
                        return false;
                    },

                    /** [write description] */
                    write: function (flag) {
                        spinner($('#customer-email'), flag, css);
                    }
                });

                return this;
            }
        });
    };
});
