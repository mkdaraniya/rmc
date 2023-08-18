define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'Swissup_Firecheckout/js/model/place-order',
    'Swissup_Firecheckout/js/model/layout',
    'ClassyLlama_AvaTax/js/model/address-model'
], function ($, modal, $t, placeOrder, layout, addressModel) {
    'use strict';

    var popupElement,
        processed = false;

    /**
     * Create modal from the element
     * @param {jQuery} element
     */
    function createModalPopup(element) {
        modal({
            'type': 'popup',
            'responsive': true,
            'innerScroll': true,
            'trigger': '.show-modal',
            'buttons': [
                {
                    text: $t('Place Order'),
                    class: 'action primary',

                    /** @inheritdoc */
                    click: function () {
                        placeOrder.placeOrder();
                    }
                }
            ]
        }, element);
    }

    /**
     * Proxy for popular modal popup methods
     * @return {Object|Boolean}
     */
    function getModalPopup() {
        if (!popupElement) {
            $('li#validate_address').wrap('<ul class="nolist fc-address-verification-popup"></ul>');
            popupElement = $('.fc-address-verification-popup');
        }

        if (!popupElement.length) {
            return false;
        }

        if (!popupElement.data('mageModal')) {
            createModalPopup(popupElement);
        }

        return {
            /**
             * Open modal popup
             */
            open: function () {
                popupElement.modal('openModal');
            },

            /**
             * Close modal popup
             */
            close: function () {
                popupElement.modal('closeModal');
            },

            /**
             * Check if popup is opened
             */
            isOpened: function () {
                return popupElement.data('mageModal').options.isOpen;
            }
        };
    }

    /**
     * If popup is not opened - prevent placing an order, but
     * show address verification popup instead.
     *
     * @param {jQuery.Event} e
     */
    function handlePlaceOrderBefore(e) {
        var popup = getModalPopup();

        if (processed || !popup || popup.isOpened()) {
            processed = false;

            if (popup) {
                popup.close();
            }

            return;
        }

        e.cancel = true; // do not place order

        $.when(placeOrder.submitShippingInformation())
            .done(function (result) {
                // @see ClassyLlama/AvaTax/Plugin/Checkout/Model/ShippingInformationManagement.php:250
                if (!result ||
                    //jscs:disable requireCamelCaseOrUpperCaseIdentifiers
                    !result.extension_attributes ||
                    !result.extension_attributes.valid_address ||
                    //jscs:enable requireCamelCaseOrUpperCaseIdentifiers
                    !$('#validate_address').is(':visible') ||
                    // @see ClassyLlama/AvaTax/view/frontend/web/js/view/checkout-validation-handler.js:54
                    (!addressModel.isDifferent() && addressModel.error() == null)) {

                    // no address to choose - place order immediately
                    processed = true;

                    return placeOrder.placeOrder();
                }

                // show address selector popup
                popup.open();
            });
    }

    /**
     * Show address verification popup before placing an order
     */
    return {
        /**
         * Init plugin
         */
        init: function () {
            if (layout.isMultistep()) {
                return;
            }

            console.log(
                'Firecheckout and ClassyLlama_AvaTax address autocorrection: ' +
                'please use multistep layout to prevent conflicts with third-party payments'
            );

            $('body').on('fc:placeOrderBefore', handlePlaceOrderBefore);
            $('body').on('click', '#validate_address .edit-address', function () {
                getModalPopup().close();
            });
        }
    };
});
