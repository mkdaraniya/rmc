define([
    'underscore'
], function (_) {
    'use strict';

    var registry = {},
        list = {
            before: {},
            after: {}
        };

    return {
        /**
         * Add sequence(what) to the given URL(which) at specified position(when)
         *
         * Examples:
         *
         *  1. Run method before each /payment-information request
         *
         *      sequence.add('before', '/payment-information', function () {
         *          return console.log('before payment-information');
         *      });
         *
         *  2. Run method after successfull /coupons request
         *
         *      sequence.add('after', '/coupons', function () {
         *          return console.log('after coupons');
         *      });
         *
         * @param {String}       when   - Sequence type (before|after)
         * @param {String|Array} which  - URL to wrap
         * @param {Function}     what   - Function to call. Return $.Deferred
         *                              object in case if your method runs async
         *                              request
         */
        add: function (when, which, what) {
            var exists;

            if (typeof which === 'string') {
                which = [which];
            }

            _.each(which, function (url) {
                if (!list[when][url]) {
                    list[when][url] = [];
                }

                // prevent multiple calls to the same function
                exists = _.find(list[when][url], function (func) {
                    return func === what;
                });

                if (!exists) {
                    list[when][url].push(what);
                    registry[url] = 1;
                }
            });
        },

        /**
         * Run methods attached to the urls similar to received url
         *
         * @param  {String} url     - URL to lookup in sequence list
         * @param  {String} when    - Possible values: before, after
         * @param  {Mixed} result   - Result of wrapped function
         * @return {Array}          - Array of $.Deferred objects
         */
        run: function (url, when, result) {
            var collection = [];

            _.each(list[when], function (callables, string) {
                if (url.indexOf(string) === -1) {
                    return;
                }

                _.each(callables, function (callable) {
                    collection.push(callable(result));
                });
            });

            return collection;
        },

        /**
         * Check if sequence is attached to given URL
         *
         * @param  {String}  url
         * @return {Boolean}
         */
        has: function (url) {
            return _.find(registry, function (func, string) {
                return url.indexOf(string) > 0;
            });
        }
    };
});
