define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        /**
         * Create modal from the element
         * @param  {jQuery} element
         */
        function createModalPopup(element) {
            modal({
                'type': 'popup',
                'clickableOverlay': false,
                'modalClass': 'iframe-modal modal-unclosable',
                'responsive': true,
                'innerScroll': true,
                'trigger': '.show-modal',
                'buttons': []
            }, element);
        }

        return target.extend({

            /**
             * Show iframe in popup
             */
            initObservable: function () {
                var self = this;

                this._super();

                this.isInAction.subscribe(function (value) {
                    if (!self.popupElement) {
                        self.popupElement = $('#iframe-warning', '.payment-method._active').parent();
                    }

                    if (!self.popupElement.length) {
                        return;
                    }

                    if (!self.popupElement.data('mageModal')) {
                        createModalPopup(self.popupElement);
                    }

                    if (!value) {
                        self.popupElement.modal('closeModal');
                    } else {
                        self.popupElement.modal('openModal');
                        self.popupElement.data('mageModal').modal.off('keydown');
                    }
                });

                return this;
            }
        });
    };
});
