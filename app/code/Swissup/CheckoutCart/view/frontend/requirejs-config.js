var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/summary/item/details': {
                'Swissup_CheckoutCart/js/view/details-mixin': true
            },
            'Magento_Checkout/js/model/resource-url-manager': {
                'Swissup_CheckoutCart/js/model/resource-url-manager-mixin': true
            }
        }
    }
};
