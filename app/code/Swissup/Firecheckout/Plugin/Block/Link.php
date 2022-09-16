<?php

namespace Swissup\Firecheckout\Plugin\Block;

class Link
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
     * @param \Magento\Checkout\Block\Link $subject
     * @param string $result
     * @return string
     */
    public function afterGetHref(
        \Magento\Checkout\Block\Link $subject,
        $result
    ) {
        if ($this->helper->isFirecheckoutEnabled()) {
            return $this->helper->getFirecheckoutUrl();
        }
        return $result;
    }
}
