define([
    'ko',
    'jquery',
    'Magento_CheckoutAgreements/js/model/agreements-modal'
], function (ko, $, agreementsModal) {
    'use strict';

    var checkoutConfig = window.checkoutConfig;

    return function (target) {
        if (!checkoutConfig || !checkoutConfig.isFirecheckout) {
            return target;
        }

        return target.extend({
            agreementId: ko.observable(null),

            /**
             * Overriden to store checkbox_id of active agreement
             *
             * @param {Object} element
             */
            showContent: function (element, e) {
                this.modalTitle(element.checkboxText);
                this.modalContent(element.content);
                agreementsModal.showModal();
                agreementsModal.setCheckboxId(
                    $(e.currentTarget).closest('label').attr('for')
                );
            }
        });
    };
});
