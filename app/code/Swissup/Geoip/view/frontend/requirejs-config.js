var config = {
    config: {
        mixins: {
            // Add region and city to the address object
            'Magento_Checkout/js/model/new-customer-address': {
                'Swissup_Geoip/js/new-customer-address-mixin': true
            }
        }
    }
};
