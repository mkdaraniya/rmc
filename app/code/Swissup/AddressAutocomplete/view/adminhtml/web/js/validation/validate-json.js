define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (target) {
        $.validator.addMethod(
            'address-autocomplete-validate-json',
            function (value) {
                try {
                    JSON.parse(value);
                } catch (e) {
                    return false;
                }

                return true;
            },
            $t('Please enter a valid json data.')
        );

        return target;
    };
});
