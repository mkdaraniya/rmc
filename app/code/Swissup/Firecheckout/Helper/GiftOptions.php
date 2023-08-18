<?php

namespace Swissup\FirecheckoutIntegrations\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class GiftOptions extends AbstractHelper
{
    /**
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->isPerOrderDisabled() && $this->isPerItemDisabled();
    }

    /**
     * @return boolean
     */
    public function isPerOrderDisabled()
    {
        return !$this->scopeConfig->getValue(
            'sales/gift_options/allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->scopeConfig->getValue(
            'sales/gift_options/wrapping_allow_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return boolean
     */
    public function isPerItemDisabled()
    {
        return !$this->scopeConfig->getValue(
            'sales/gift_options/allow_items',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ) && !$this->scopeConfig->getValue(
            'sales/gift_options/wrapping_allow_items',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
