define([
    'Magento_Ui/js/lib/view/utils/async',
    'mage/utils/wrapper',
    './watcher',
    './status'
], function ($, wrapper, watcher, status) {
    'use strict';

    /**
     * Watch for some fields and change the status of dependent fields.
     * Allows to hide, made optional, and made required field.
     */
    function Dependency(settings) {
        var react = settings.react,
            watcherInstance;

        delete settings.react;

        $.each(react, function (key, value) {
            if (settings.scope) {
                delete react[key];
                key = settings.scope + ' ' + key;
                react[key] = value;
            }

            if (typeof value === 'string') {
                value = value.split('|');
                react[key] = {
                    match: value[0],
                    unmatch: value[1]
                };
            }
        });

        /**
         * Process `react` rules, when conditions are matched
         */
        function match(afterMatch) {
            $.each(react, function (key, rules) {
                status(key, rules.match);
            });

            if (typeof afterMatch === 'function') {
                afterMatch();
            }
        }

        /**
         * Process `react` rules, when conditions are not matched
         */
        function unmatch(afterUnmatch) {
            $.each(react, function (key, rules) {
                status(key, rules.unmatch);
            });

            if (typeof afterUnmatch === 'function') {
                afterUnmatch();
            }
        }

        settings.match = settings.match ?
            wrapper.wrap(settings.match, match) : match;
        settings.unmatch = settings.unmatch ?
            wrapper.wrap(settings.unmatch, unmatch) : unmatch;

        watcherInstance = watcher(settings);

        return {
            check: watcherInstance.check
        };
    }

    /**
     * @param {String|Element|Object} element
     * @param {Object|Function} settings
     */
    return function (element, settings) {
        if (settings) {
            settings.react = settings;
            settings.watch = {};
            settings.watch[element] = '*';
        } else {
            settings = element;
        }

        return new Dependency(settings);
    };
});
