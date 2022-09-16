define([
    'jquery',
    'Magento_Ui/js/lib/validation/utils',
    'moment',
    'mage/translate'
], function ($, utils, moment) {
    'use strict';

    return function (target) {

        target['delivery-date-validate-date'] = {
            /**
             * @param  {String} value
             * @param  {Object} params
             * @param  {Object} additionalParams
             * @return {Boolean}
             */
            handler: function (value, params, additionalParams) {
                var test = moment(value, additionalParams.dateFormat, true);

                return utils.isEmptyNoTrim(value) || test.isValid();
            },
            message: $.mage.__('Please enter a valid date.')
        };

        return target;
    };
});
