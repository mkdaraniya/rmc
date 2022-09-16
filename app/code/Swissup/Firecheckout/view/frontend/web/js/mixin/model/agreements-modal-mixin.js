define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function ($, modal, $t) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Store checkbox id, @see Swissup_Firecheckout/js/view/checkout-agreements-mixin
         * @param {Number} id
         */
        target.setCheckboxId = function (id) {
            target.checkboxId = id;
        };

        /**
         * Overriden to add "I agree" button
         *
         * @param {HTMLElement} element
         */
        target.createModal = function (element) {
            var options;

            target.modalWindow = element;
            options = {
                'type': 'popup',
                'modalClass': 'agreements-modal',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.show-modal',
                'buttons': [
                    {
                        text: $t('I Agree'),
                        class: 'action secondary action-agree',

                        /** @inheritdoc */
                        click: function () {
                            $('[id="' + target.checkboxId + '"]')
                                .prop('checked', true)
                                .change();
                            this.closeModal();
                        }
                    }
                ]
            };
            modal(options, $(target.modalWindow));
        };

        return target;
    };
});
