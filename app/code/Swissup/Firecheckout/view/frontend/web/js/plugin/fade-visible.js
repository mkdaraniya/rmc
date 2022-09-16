define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/lib/knockout/bindings/fadeVisible'
], function ($, _, ko) {
    'use strict';

    /** Disable js animation as it's not attractive and makes weird jumps sometimes */
    ko.bindingHandlers.fadeVisible.update = _.wrap(
        ko.bindingHandlers.fadeVisible.update,
        function (o, element, valueAccessor) {
            var value = valueAccessor();

            if (!$(element).closest('#checkoutSteps').length) {
                return o(element, valueAccessor);
            }

            if (ko.unwrap(value)) {
                $(element).show();
            } else {
                $(element).hide();
            }
        }
    );
});
