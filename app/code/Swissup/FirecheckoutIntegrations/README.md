# Firecheckout integrations

## Installation

### For clients

Please do not install this module. It will be installed automatically as
firecheckout dependency.

### For developers

Use this approach if you have access to our private repositories!

```bash
cd <magento_root>
composer config repositories.swissup composer https://docs.swissuplabs.com/packages/
composer require swissup/firecheckout-integrations:dev-master --prefer-source
bin/magento module:enable Swissup_FirecheckoutIntegrations
bin/magento setup:upgrade
```
