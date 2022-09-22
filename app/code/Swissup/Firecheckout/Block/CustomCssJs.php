<?php

namespace Swissup\Firecheckout\Block;

class CustomCssJs extends \Magento\Framework\View\Element\Template
{
    const CONFIG_PATH_CSS = 'firecheckout/custom_css_js/css';

    const CONFIG_PATH_JS = 'firecheckout/custom_css_js/js';

    /**
     * @var string
     */
    protected $_template = 'Swissup_Firecheckout::custom_css_js.phtml';

    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Swissup\Firecheckout\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    public function getCss()
    {
        return $this->helper->getConfigValue(self::CONFIG_PATH_CSS);
    }

    /**
     * @return string
     */
    public function getJs()
    {
        return str_replace('define([', 'require([', (string)$this->helper->getConfigValue(self::CONFIG_PATH_JS));
    }
}
