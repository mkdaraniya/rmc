<?php

namespace Swissup\Firecheckout\Plugin\Block;

class OnepageLink
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
     * @param \Magento\Checkout\Block\Onepage\Link $subject
     * @param string $result
     * @return string
     */
    public function afterGetCheckoutUrl(
        \Magento\Checkout\Block\Onepage\Link $subject,
        $result
    ) {
        if ($this->helper->isFirecheckoutEnabled()) {
            return $this->helper->getFirecheckoutUrl();
        }
        return $result;
    }

    /**
     * @param \Magento\Checkout\Block\Onepage\Link $subject
     * @param boolean $result
     * @return boolean
     */
    public function afterIsPossibleOnepageCheckout(
        \Magento\Checkout\Block\Onepage\Link $subject,
        $result
    ) {
        if ($this->helper->isFirecheckoutEnabled()) {
            return true;
        }
        return $result;
    }
}
