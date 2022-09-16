<?php

namespace Swissup\SubscribeAtCheckout\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;

class Config extends AbstractHelper
{
    /**
     * @var string
     */
    const CONFIG_PATH_ENABLED = 'subscribe_at_checkout/general/enabled';

    /**
     * @var string
     */
    const CONFIG_PATH_FIELD_LABEL = 'subscribe_at_checkout/general/field_label';

    /**
     * @var string
     */
    const CONFIG_PATH_FIELD_NOTICE = 'subscribe_at_checkout/general/field_notice';

    /**
     * @var string
     */
    const CONFIG_PATH_DEFAULT_VALUE = 'subscribe_at_checkout/general/default_value';

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Get config to use for component rendering
     *
     * @return array
     */
    public function getComponentConfig()
    {
        return [
            'template' => 'Swissup_SubscribeAtCheckout/form/element/subscription',
            'elementTmpl' => 'ui/form/element/checkbox',
            'label' => $this->getFieldLabel(),
            'notice' => $this->getFieldNotice(),
            'value' => $this->getDefaultCheckboxValue(),
            'componentDisabled' => $this->isComponentDisabled(),
        ];
    }

    /**
     * Get config to use for component rendering inside shipping address section
     *
     * @return array
     */
    public function getComponentConfigForStandardQuote()
    {
        $config = $this->getComponentConfig();

        if ($this->isVirtualQuote()) {
            $config['componentDisabled'] = true;
        }

        return $config;
    }

    /**
     * Get config to use for component rendering inside payment methods section
     *
     * @return array
     */
    public function getComponentConfigForVirtualQuote()
    {
        $config = $this->getComponentConfig();

        if (!$this->isVirtualQuote()) {
            $config['componentDisabled'] = true;
        }

        return $config;
    }

    /**
     * Retrieve isEnabled flag
     *
     * @return boolean
     */
    public function isModuleEnabled()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return boolean
     */
    private function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return boolean
     */
    public function isComponentDisabled()
    {
        return !$this->isModuleEnabled() || $this->isCustomerLoggedIn();
    }

    /**
     * @return string
     */
    public function getFieldLabel()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_FIELD_LABEL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getFieldNotice()
    {
        return $this->scopeConfig->getValue(
            self::CONFIG_PATH_FIELD_NOTICE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return boolean
     */
    public function getDefaultCheckboxValue()
    {
        return (bool) $this->scopeConfig->getValue(
            self::CONFIG_PATH_DEFAULT_VALUE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    private function isVirtualQuote()
    {
        return $this->checkoutSession->getQuote()->isVirtual();
    }
}
