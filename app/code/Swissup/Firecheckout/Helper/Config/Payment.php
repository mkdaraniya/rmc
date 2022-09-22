<?php

namespace Swissup\Firecheckout\Helper\Config;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Swissup\Firecheckout\Model\Config\Source\BillingAddressDisplayOptions;
use Swissup\Firecheckout\Model\Config\Source\BillingAddressSaveModes;

class Payment extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_DEFAULT_PAYMENT_METHOD = 'firecheckout/payment/default_method';

    /**
     * @var string
     */
    const CONFIG_PATH_DISPLAY_BILLING_ADDRESS_TITLE = 'firecheckout/payment/display_billing_address_title';

    /**
     * @var string
     */
    const CONFIG_PATH_DISPLAY_BILLING_ADDRESS_ON = 'firecheckout/payment/display_billing_address_on';

    /**
     * @var string
     */
    const CONFIG_PATH_BILLING_ADDRESS_SAVE_MODE = 'firecheckout/payment/billing_address_save_mode';

    /**
     * @var \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
     */
    private $firecheckoutHelper;

    /**
     * @param Context $context
     * \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
     */
    public function __construct(
        Context $context,
        \Swissup\Firecheckout\Helper\Data $firecheckoutHelper
    ) {
        parent::__construct($context);

        $this->firecheckoutHelper = $firecheckoutHelper;
    }

    /**
     * @return string
     */
    public function getDefaultPaymentMethod()
    {
        return $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_DEFAULT_PAYMENT_METHOD,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * @return mixed
     */
    public function getDisplayBillingAddressOn()
    {
        $value = $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_DISPLAY_BILLING_ADDRESS_ON
        );

        if (!$value || $value === BillingAddressDisplayOptions::OPTION_MAGENTO_CONFIG) {
            $this->firecheckoutHelper->getConfigValue(
                'checkout/options/display_billing_address_on'
            );
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getBillingAddressTitle()
    {
        return $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_DISPLAY_BILLING_ADDRESS_TITLE
        ) ? __('Billing Address') : '';
    }

    /**
     * @return array
     */
    public function getBillingAddressJsConfig()
    {
        return [
            'title' => $this->getBillingAddressTitle(),
            'position' => $this->getDisplayBillingAddressOn(),
        ];
    }

    /**
     * @return boolean
     */
    public function isBillingAddressInstantSaveDisabled()
    {
        return $this->firecheckoutHelper->getConfigValue(
            self::CONFIG_PATH_BILLING_ADDRESS_SAVE_MODE
        ) !== BillingAddressSaveModes::MODE_INSTANT;
    }
}
