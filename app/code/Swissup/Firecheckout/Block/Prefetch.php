<?php

namespace Swissup\Firecheckout\Block;

class Prefetch extends \Magento\Framework\View\Element\Text
{
    /**
     * @var \Swissup\Firecheckout\Helper\Config
     */
    private $helper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\JsBuild
     */
    private $jsBuild;

    /**
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\JsBuild $jsBuild
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\JsBuild $jsBuild,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->helper = $helper;
        $this->jsBuild = $jsBuild;
    }

    /**
     * @return string
     */
    public function getText()
    {
        if (!$this->helper->isFirecheckoutEnabled() ||
            !$this->jsBuild->isEnabled() ||
            !$this->jsBuild->isPrefetchEnabled()
        ) {
            return '';
        }

        return $this->getData('text');
    }
}
