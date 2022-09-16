var config = {
    config: {
        mixins: {
            // Save shipping information before order was placed
            'mage/storage': {
                'Swissup_Firecheckout/js/mixin/model/storage-mixin': true
            },
            // 1. don't hide the message too quick
            // 2. hide the message after 8 seconds instead of 5
            'Magento_Ui/js/view/messages': {
                'Swissup_Firecheckout/js/mixin/view/messages-mixin': true
            },
            // Scroll to error
            'Magento_Checkout/js/model/error-processor': {
                'Swissup_Firecheckout/js/mixin/model/error-processor-mixin': true
            },
            // Open steps for all modes except multistep-wizard
            'Magento_Checkout/js/model/step-navigator': {
                'Swissup_Firecheckout/js/mixin/model/step-navigator-mixin': true
            },
            // Add set-shipping-method and set-shipping-address urls
            'Magento_Checkout/js/model/resource-url-manager': {
                'Swissup_Firecheckout/js/mixin/model/resource-url-manager-mixin': true
            },
            // Set street as an array, if it's not set. Fixes Magento_Braintree issue.
            'Magento_Checkout/js/model/address-converter': {
                'Swissup_Firecheckout/js/mixin/model/address-converter-mixin': true
            },
            // Don't validate email on payment step unless it's a virtual purchase
            'Magento_Checkout/js/model/customer-email-validator': {
                'Swissup_Firecheckout/js/mixin/model/customer-email-validator-mixin': true
            },
            // Disable loader for specific components
            'Magento_Checkout/js/model/full-screen-loader': {
                'Swissup_Firecheckout/js/mixin/model/full-screen-loader-mixin': true
            },
            // Reduce validateDelay
            'Magento_Checkout/js/model/shipping-rates-validator': {
                'Swissup_Firecheckout/js/mixin/model/abstract-validator-mixin': true
            },
            // Reduce validateDelay
            'Magento_Checkout/js/model/billing-address-postcode-validator': {
                'Swissup_Firecheckout/js/mixin/model/abstract-validator-mixin': true
            },
            // Magento 2.2.8 fix for mistakenly equal billing and shipping addresses
            'Magento_Checkout/js/action/select-billing-address': {
                'Swissup_Firecheckout/js/mixin/action/select-billing-address-mixin': true
            },
            // Call payment methods recalculation on address data change
            'Magento_Checkout/js/action/select-shipping-address': {
                'Swissup_Firecheckout/js/mixin/action/select-shipping-address-mixin': true
            },
            // Prevent 400 Bad Request response when email is not filled in
            'Magento_Checkout/js/action/set-payment-information-extended': {
                'Swissup_Firecheckout/js/mixin/action/set-payment-information-extended-mixin': true
            },
            // Save/restore payment form data, prevent section update if needed
            'Magento_Checkout/js/model/payment-service': {
                'Swissup_Firecheckout/js/mixin/model/payment-service-mixin': true
            },
            // Prevent from saving invalid/empty billing address
            'Magento_Checkout/js/view/billing-address': {
                'Swissup_Firecheckout/js/mixin/view/billing-address-mixin': true
            },
            // Fix lost focus on email field
            'Magento_Checkout/js/view/form/element/email': {
                'Swissup_Firecheckout/js/mixin/view/email-mixin': true
            },
            // Dispatch fc:validate-shipping-information event
            'Magento_Checkout/js/view/shipping': {
                'Swissup_Firecheckout/js/mixin/view/shipping-mixin': true
            },
            // Always show shipping information
            'Magento_Checkout/js/view/shipping-information': {
                'Swissup_Firecheckout/js/mixin/view/shipping-information-mixin': true
            },
            // Always show order totals
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Swissup_Firecheckout/js/mixin/view/summary/abstract-total-mixin': true
            },
            // Always show cart items
            'Magento_Checkout/js/view/summary/cart-items': {
                'Swissup_Firecheckout/js/mixin/view/summary/cart-items-mixin': true
            },
            // Select default shipping/payment methods
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Swissup_Firecheckout/js/mixin/model/checkout-data-resolver-mixin': true
            },
            // Set agreement checkbox id to be able to click 'I Agree' button in argeements popup
            'Magento_CheckoutAgreements/js/view/checkout-agreements': {
                'Swissup_Firecheckout/js/mixin/view/checkout-agreements-mixin': true
            },
            // Show 'I Agree' button in argeements popup
            'Magento_CheckoutAgreements/js/model/agreements-modal': {
                'Swissup_Firecheckout/js/mixin/model/agreements-modal-mixin': true
            },
            // Move iframe into modal popup
            'Magento_Paypal/js/view/payment/method-renderer/iframe-methods': {
                'Swissup_Firecheckout/js/mixin/view/payment/paypal/method-renderer/iframe-methods-mixin': true
            },
            // Disable validation when using onestep layout
            'Magento_Paypal/js/view/payment/method-renderer/in-context/checkout-express': {
                'Swissup_Firecheckout/js/mixin/view/payment/paypal/method-renderer/in-context/checkout-express-mixin': true
            }
        }
    }
};
