define([
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'ko',
    'Magento_Ui/js/lib/validation/rules',
    './placeholder'
], function ($, _, ko, rules, placeholder) {
    'use strict';

    /**
     * Validate the field. Triggered by `blur` event.
     */
    function validate(e) {
        var el = e.target,
            koElement = ko.dataFor(el),
            lazyRules = $(el).data('fc-validators') || [];

        apply(el, lazyRules);

        koElement.onUpdate();
    }

    /**
     * Remove all validators to prevent instant validation.
     * Put them back when `blur` event is triggered.
     *
     * @param {Element} el
     */
    function applyLazyValidation(el) {
        var koElement = ko.dataFor(el),
            tmp = $(el).data('fc-validators') || [];

        // remove all validators to prevent instant validation
        $.each(koElement.validation, function (key, value) {
            tmp[key] = value;
        });
        $(el).data('fc-validators', tmp);

        /**
         * Remove declared validators
         */
        function removeValidation() {
            koElement.validation = {};
            $(el).off('focus', removeValidation);
        }
        $(el).off('focus', removeValidation);
        $(el).on('focus', removeValidation);
        $(el).off('blur', validate);
        $(el).on('blur', validate);
    }

    /**
     * Update placeholder with asterisk if needed
     *
     * @param {Element} el
     */
    function updatePlaceholder(el) {
        var koElement = ko.dataFor(el),
            placeholderValue = $(el).attr('placeholder'),
            asterisk = ' *',
            hasAsterisk;

        if (placeholderValue && placeholderValue.length) {
            hasAsterisk = placeholderValue.indexOf(asterisk, placeholderValue.length - asterisk.length) > -1;

            if (koElement.validation.required && !hasAsterisk) {
                placeholder(el, placeholderValue + asterisk);
            } else if (!koElement.validation.required && hasAsterisk) {
                placeholder(el, placeholderValue.substr(0, placeholderValue.length - asterisk.length));
            }
        }
    }

    /**
     * @param {Element} el
     * @param {Object|Boolean} settings
     */
    function apply(el, settings) {
        var koElement = ko.dataFor(el);

        if (!koElement || !koElement.validation) {
            return;
        }

        if (settings === false) {
            settings = {
                required: false
            };
        }

        if (!settings || settings === true) {
            settings = {
                required: true
            };
        }

        settings = $.extend({}, settings);

        if (typeof settings.required !== 'undefined') {
            settings['required-entry'] = settings.required;

            if (typeof koElement.required === 'function') {
                koElement.required(settings.required);
            } else {
                koElement.required = settings.required;
            }
        }

        // fix for the third-party fields
        if (typeof koElement.validation.length !== 'undefined') {
            koElement.validation = _.object(
                _.values(koElement.validation),
                _.keys(koElement.validation)
            );
        }

        $.each(settings, function (key, value) {
            if (key === 'lazy') {
                return;
            }

            if (key.indexOf('fc-custom-rule') === 0) {
                if (!rules[key]) {
                    rules[key] = value;
                }
                value = true;
            }

            koElement.validation[key] = value;
        });

        updatePlaceholder(el);

        if (settings.lazy) {
            delete settings.lazy;
            applyLazyValidation(el);
        }
    }

    /**
     * @param  {String} selector
     * @param  {Object} settings
     */
    return function (selector, settings) {
        if (typeof selector === 'string') {
            $.async(selector, function (el) {
                apply(el, settings);
            });
        } else {
            apply(selector, settings);
        }
    };
});
