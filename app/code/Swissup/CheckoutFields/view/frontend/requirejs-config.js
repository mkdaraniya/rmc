var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Swissup_CheckoutFields/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Swissup_CheckoutFields/js/model/set-payment-information-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information-extended': {
                'Swissup_CheckoutFields/js/model/set-payment-information-extended-mixin': true
            },
            'Magento_Ui/js/form/element/abstract': {
                'Swissup_CheckoutFields/js/mixin/form/element/abstract-mixin': true
            }
        }
    }
};
