<?php

namespace Swissup\Firecheckout\Plugin\Block;

class Cart
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    protected $helper;

    /**
     * @param \Swissup\Firecheckout\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Cart $subject
     * @param string $result
     * @return string
     */
    public function afterGetCheckoutUrl(
        \Magento\Checkout\Block\Cart $subject,
        $result
    ) {
        if ($this->helper->isFirecheckoutEnabled()) {
            return $this->helper->getFirecheckoutUrl();
        }
        return $result;
    }
}
