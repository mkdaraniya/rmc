var config = {
    config: {
        mixins: {
            // fix for the uk_UA, de_DE locales
            'mage/utils/misc': {
                'Swissup_DeliveryDate/js/mixin/utils/misc-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Swissup_DeliveryDate/js/mixin/shipping-mixin': true
            },
            // Compatibility with Magento < 2.2.2
            'mage/storage': {
                'Swissup_DeliveryDate/js/mixin/storage-mixin': true
            },
            // Magento >= 2.2.2
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'Swissup_DeliveryDate/js/mixin/payload-extender-mixin': true
            },
            'Magento_Ui/js/lib/validation/rules': {
                'Swissup_DeliveryDate/js/validation/delivery-date-validate-date': true
            }
        }
    }
};
