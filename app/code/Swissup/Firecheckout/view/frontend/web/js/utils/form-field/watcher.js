define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore'
], function ($, _) {
    'use strict';

    /**
     * Allows to watch over the form field values.
     *
     * Settings example:
     * {
     *   scope: '.checkout-shipping-address',        // watch for elements inside this parent only
     *   watch: {
     *       '[name="city"]': '*',                   // Any non empty value
     *       '[name="country_id"]': ['US', 'GB'],    // Selectbox
     *       '[name="customer-subscription"]': true  // Checkbox
     *   },
     *   match: function () {
     *       console.log('match');
     *   },
     *   unmatch: function () {
     *       console.log('unmatch');
     *   },
     *   always: function (result) {
     *       console.log(result);
     *   }
     * }
     */
    function Watcher(settings) {
        var i = 0,
            limit = 0,
            ready = false,
            config;

        config = $.extend({
            watch: {},
            match: $.noop,
            unmatch: $.noop,
            always: $.noop
        }, settings);

        // transform possible values into array
        _.each(config.watch, function (value, key) {
            config.watch[key] = [].concat(value);
        });

        /**
         * @param  {Element} el
         * @return {Boolean}
         */
        function isCheckbox(el) {
            return el.tagName === 'INPUT' && el.type === 'checkbox';
        }

        /**
         * @param  {Element} el
         * @return {Boolean}
         */
        function isRadio(el) {
            return el.tagName === 'INPUT' && el.type === 'radio';
        }

        /**
         * @param  {Array} possibleValues
         * @param  {Element} el
         * @return {Boolean}
         */
        function check(possibleValues, el) {
            var domEl = $(el).get(0),
                value = $(el).val(),
                stringValue = value + '';

            if (isCheckbox(domEl) || isRadio(domEl)) {
                if ($(el).prop('checked') &&
                    (possibleValues.indexOf('*') > -1 ||
                        possibleValues.indexOf(true) > -1 ||
                        possibleValues.indexOf(value) > -1)) {

                    return true;
                }

                if (!$(el).prop('checked') && possibleValues.indexOf(false) > -1) {
                    return true;
                }

                return false;
            }

            if (possibleValues.indexOf('*') > -1 && stringValue.length) {
                return true;
            }

            return possibleValues.indexOf(value) > -1;
        }

        /**
         * @param  {Array} possibleValues
         * @param  {String|Element} selector
         * @return {Boolean}
         */
        function checkSelector(possibleValues, selector) {
            // if selector returns multiple elements - return true if any of them match
            return _.some($(selector, config.scope), function (singleEl) {
                return check(possibleValues, singleEl);
            });
        }

        /**
         * Check all rules and call match or unmatch function
         */
        function checkAll(el) {
            var result;

            if (!ready) {
                return;
            }

            result = _.every(config.watch, checkSelector);

            if (result) {
                config.match(el);
            } else {
                config.unmatch(el);
            }

            config.always(result, el);
        }

        limit = _.keys(config.watch).length;

        $.each(config.watch, function (selector) {
            selector = config.scope ? config.scope + ' ' + selector : selector;

            $('body').on('change', selector, function () {
                checkAll(this);
            });

            $.async(selector, function (el) {
                if (++i < limit) {
                    return;
                }
                ready = true;
                checkAll(el);
            });
        });

        return {
            check: checkAll
        };
    }

    /**
     * @param {String|Element|Object} element
     * @param {Object|Function} settings
     */
    return function (element, settings) {
        if (settings) {
            if (typeof settings === 'function') {
                settings = {
                    always: settings
                };
            }
            settings.watch = {};
            settings.watch[element] = '*';
        } else {
            settings = element;
        }

        return new Watcher(settings);
    };
});
