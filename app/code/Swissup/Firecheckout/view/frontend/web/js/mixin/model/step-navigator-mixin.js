define([
    'jquery',
    'ko',
    'mage/utils/wrapper',
    'Magento_Customer/js/model/customer',
    'Swissup_Firecheckout/js/model/layout',
    'Swissup_Firecheckout/js/utils/harlem'
], function ($, ko, wrapper, customer, layout, harlem) {
    'use strict';

    var checkoutConfig = window.checkoutConfig,
        processedSteps = [];

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        target.handleHash = wrapper.wrap(
            target.handleHash,
            function (original) {
                var hashString = window.location.hash.replace('#', '');

                if (hashString === 'cart') {
                    return;
                }

                if (hashString === 'email-address' && customer.isLoggedIn()) {
                    window.location.hash = this.steps().sort(this.sortItems)[1].code;
                }

                return original();
            }
        );

        target.setHash = wrapper.wrap(
            target.setHash ? target.setHash : function () {},
            function (original, hash) {
                if (hash === 'cart') { // see js/plugin/step-cart.js
                    hash = this.steps().sort(this.sortItems)[1].code;
                }

                return original(hash);
            }
        );

        target.registerStep = wrapper.wrap(
            target.registerStep,
            function (original, code, alias, title, isVisible) {
                original.apply(Array.prototype.slice.call(arguments, 1));

                if (!layout.isMultistep()) {
                    isVisible(true);
                }
            }
        );

        target.steps.subscribe(function (steps) {
            $.each(steps, function (i, step) {
                var isVisible = step.isVisible;

                if (processedSteps.indexOf(step.code) > -1) {
                    return;
                }

                processedSteps.push(step.code);

                if (isVisible.subscribe) {
                    isVisible.subscribe(function (value) {
                        step.isVisible(value);
                    });
                }

                step.isVisible = ko.pureComputed({
                    /** [read description] */
                    read: function () {
                        return layout.isMultistep() ? isVisible() : true;
                    },

                    /** [write description] */
                    write: function (flag) {
                        if (!layout.isMultistep()) {
                            flag = true;
                        }

                        if (isVisible() !== flag) {
                            isVisible(flag);
                        }

                        $('body').toggleClass('fc-step-' + step.code, flag);
                    }
                });

                // Magento_PurchaseOrder fix
                step.isVisible(step.isVisible());
            });
        });

        target.navigateTo = wrapper.wrap(
            target.navigateTo,
            function (originalAction, code, scrollToElementId) {
                var scrollTo = $('#' + code);

                if (code !== 'cart') {
                    if (scrollToElementId && $('#' + scrollToElementId).length) {
                        scrollTo = $('#' + scrollToElementId);
                    }

                    // parent logic will not scroll the viewport because of
                    // isProcessed check. Scroll it by ourself.
                    if (!target.isProcessed(code)) {
                        $('body, html').animate({
                            scrollTop: scrollTo.offset().top - 20
                        }, 200, function () {
                            harlem.shake(scrollTo.find('.step-title'));
                        });
                    }

                    return originalAction(code, scrollToElementId);
                }

                target.steps().forEach(function (element) {
                    if (element.code === code) {
                        element.navigate();
                    }
                });
            }
        );

        return target;
    };
});
