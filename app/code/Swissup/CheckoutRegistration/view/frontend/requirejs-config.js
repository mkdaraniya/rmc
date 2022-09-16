var config = {
    config: {
        mixins: {
            // Validate passwords on the shipping step
            'Magento_Checkout/js/view/shipping': {
                'Swissup_CheckoutRegistration/js/mixin/shipping-mixin': true
            },
            // Send data when saving shipping information
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Swissup_CheckoutRegistration/js/mixin/shipping-payload-extender-mixin': true
            },
            // Send data when virtual quote is used
            'Magento_Checkout/js/action/place-order': {
                'Swissup_CheckoutRegistration/js/mixin/place-order-mixin': true
            },
            // Send data when virtual quote is used
            'Magento_Checkout/js/action/set-payment-information': {
                'Swissup_CheckoutRegistration/js/mixin/set-payment-information-mixin': true
            },
            // Send data when virtual quote is used
            'Magento_Checkout/js/action/set-payment-information-extended': {
                'Swissup_CheckoutRegistration/js/mixin/set-payment-information-extended-mixin': true
            }
        }
    }
};
