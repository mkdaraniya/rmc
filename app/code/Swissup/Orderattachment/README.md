# Order Attachments

Magento Order Attachments module adds ability to attach files to order. It also
provides ability to include attachment links to email template. All attached
files are protected from public access and accessible via private generated
links only.

See more info at our [docs](http://docs.swissuplabs.com/m2/extensions/order-attachments/)

### Installation

### For clients

There are several ways to install extension for clients:

 1. If you've bought the product at Magento's Marketplace - use
    [Marketplace installation instructions](https://docs.magento.com/marketplace/user_guide/buyers/install-extension.html)
 2. Otherwise, you have two options:
    - Install the sources directly from [our repository](https://docs.swissuplabs.com/m2/extensions/order-attachments/installation/composer/) - **recommended**
    - Download archive and use [manual installation](https://docs.swissuplabs.com/m2/extensions/order-attachments/installation/manual/)

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/module-orderattachment:dev-master --prefer-source
bin/magento module:enable\
    Swissup_Core\
    Swissup_Checkout\
    Swissup_Orderattachment
bin/magento setup:upgrade
```
