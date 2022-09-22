define([
    'jquery',
    'underscore',
    'mage/utils/wrapper',
    'Swissup_Firecheckout/js/model/storage-sequence'
], function ($, _, wrapper, sequence) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    /**
     * @param  {Object} target
     * @return {Object}
     */
    return function (target) {
        var requests = {
            get: {},
            post: {},
            put: {},
            delete: {}
        };

        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Add global=false param to prevent redundant sections update requests
         *
         * @param {Function} o
         * @param {String} url
         */
        function _postParamsProxy(o, url) {
            var args = Array.prototype.slice.call(arguments, 1),
                pattern = /\/shipping-information|\/shipping-method|\/billing-address|\/gift-message/;

            if (args.length < 3 && url.match(pattern)) {
                args[2] = false;
            }

            return o.apply(target, args);
        }

        target.post = wrapper.wrap(target.post, _postParamsProxy);

        /**
         * Prevents multiple requests to the same url using same method at a time
         *
         * @param {Object} memo
         * @return {Function}
         */
        function _debounceProxy(memo) {
            return function (original, url) {
                var args = arguments,
                    abort,
                    callback,
                    deferred;

                /** [callback description] */
                callback = function (result, method) {
                    if (!memo[url]) {
                        return;
                    }

                    memo[url].result[method](result);
                    delete memo[url];
                };

                /** [abort description] */
                abort = function () {
                    if (!memo[url] || !memo[url].xhr) {
                        return;
                    }

                    memo[url].xhr.abort();
                };

                if (!memo[url]) {
                    deferred = $.Deferred();
                    deferred.abort = abort;
                    deferred.error = deferred.fail;
                    deferred.success = deferred.done;
                    deferred.complete = deferred.always;

                    memo[url] = {
                        result: deferred,
                        debounced: _.debounce(function () {
                            if (!memo[url]) {
                                return;
                            }

                            memo[url].xhr = original.apply(target, Array.prototype.slice.call(args, 1));
                            memo[url].xhr.then(
                                function (response) {
                                    callback(response, 'resolve');
                                },
                                function (response) {
                                    callback(response, 'reject');
                                }
                            );
                        }, 100)
                    };
                }

                memo[url].debounced();

                return memo[url].result;
            };
        }

        target.get    = wrapper.wrap(target.get, _debounceProxy(requests.get));
        target.post   = wrapper.wrap(target.post, _debounceProxy(requests.post));
        target.put    = wrapper.wrap(target.put, _debounceProxy(requests.put));
        target.delete = wrapper.wrap(target.delete, _debounceProxy(requests.delete));

        /**
         * Runs sequence before and after original action
         *
         * @param  {Function} o
         * @param  {String} url
         */
        function _proxy(o, url) {
            var args = arguments,
                wrappedOriginal,
                wrappedAfter;

            if (sequence.has(url)) {
                /**
                 * Wrapped original function to use in `then` chain
                 * @return {Promise}
                 */
                wrappedOriginal = function () {
                    return o.apply(
                        target,
                        Array.prototype.slice.call(args, 1)
                    );
                };

                /**
                 * Wrapped sequence.run to use in `then` chain
                 * @param  {Mixed} result
                 * @return {Promise}
                 */
                wrappedAfter = function (result) {
                    sequence.run(url, 'after', result);

                    return result;
                };

                return $.when.apply($, sequence.run(url, 'before'))
                    .then(wrappedOriginal, wrappedOriginal)
                    .then(wrappedAfter, wrappedAfter);
            }

            return o.apply(
                target,
                Array.prototype.slice.call(args, 1)
            );
        }

        // Wrap all methods into _proxy call
        target.get    = wrapper.wrap(target.get, _proxy);
        target.post   = wrapper.wrap(target.post, _proxy);
        target.put    = wrapper.wrap(target.put, _proxy);
        target.delete = wrapper.wrap(target.delete, _proxy);

        return target;
    };
});
