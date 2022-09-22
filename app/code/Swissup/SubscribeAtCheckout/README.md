# Subscribe at Checkout

## Installation

### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/subscribe-at-checkout/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/subscribe-at-checkout/installation/manual/)

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer http://swissup.github.io/packages/
composer require swissup/subscribe-at-checkout:dev-master --prefer-source
bin/magento module:enable\
    Swissup_Core\
    Swissup_Checkout\
    Swissup_SubscribeAtCheckout

bin/magento setup:upgrade
```
