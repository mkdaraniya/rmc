define([
    'mage/utils/wrapper',
    'Swissup_Checkout/js/scroll-to-error'
], function (wrapper, scrollToError) {
    'use strict';

    return function (target) {
        target.process = wrapper.wrap(
            target.process,
            function (originalAction, response, messageContainer) {
                var result = originalAction(response, messageContainer);

                scrollToError();

                return result;
            }
        );

        return target;
    };
});
