define([
    'underscore',
    'Magento_Ui/js/form/element/abstract'
], function (_, Element) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'ui/form/field',
            elementTmpl: 'ui/form/element/date',
            validationParams: {
                dateFormat: '${ $.options.dateFormat }'
            },
            holidays: [],
            excludedWeekdays: []
        },

        /**
         * Initializes regular properties of instance.
         *
         * @return {Element}
         */
        initConfig: function () {
            var self = this,
                momentFormat;

            this._super();

            // convert calendar format to moment.js format. (utils method is not sufficient)
            momentFormat = this.validationParams.dateFormat;
            momentFormat = momentFormat.replace('dd', 'DD');        // 01 to 31
            momentFormat = momentFormat.replace('d', 'D');          // 1 to 31
            momentFormat = momentFormat.replace('mm', 'MM');        // ui2moment
            momentFormat = momentFormat.replace('EEEE', 'dddd');    // Sunday through Saturday
            momentFormat = momentFormat.replace('EEE', 'ddd');      // Sun through Sat
            momentFormat = momentFormat.replace('o', 'DDDD');       // 1 to 365
            this.validationParams.dateFormat = momentFormat;

            /**
             * @param  {Date} date
             * @return {Array}
             */
            this.options.beforeShowDay = function (date) {
                return [!self.isExcludedWeekday(date) && !self.isHoliday(date), ''];
            };

            return this;
        },

        /**
         * @param  {Date} date
         * @return {Boolean}
         */
        isExcludedWeekday: function (date) {
            return this.excludedWeekdays.indexOf(date.getDay()) !== -1;
        },

        /**
         * @param  {Date} date
         * @return {Boolean}
         */
        isHoliday: function (date) {
            date = date.setHours(0, 0, 0, 0);

            return _.some(this.holidays, function (holiday) {
                return date === (new Date(holiday)).setHours(0, 0, 0, 0);
            });
        }
    });
});
