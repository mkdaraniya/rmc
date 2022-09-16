# CheckoutRegistration

CheckoutRegistration - is a Magento2 module that adds ability to customize
customer registration process at checkout page:

 -  Default mode (No changes to Magento logic)
 -  Guest checkout only
 -  Optional registration at checkout page
 -  Required registration at checkout page
 -  Automatic registration in background

## Installation

### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/checkout-registration/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/checkout-registration/installation/manual/)

### For developers

Use this approach if you have access to our private repositories!

```bash
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-checkout-registration:dev-master --prefer-source
bin/magento setup:upgrade
```
