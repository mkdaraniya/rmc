var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Swissup_SubscribeAtCheckout/js/model/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Swissup_SubscribeAtCheckout/js/model/set-payment-information-mixin': true
            }
        }
    }
};
