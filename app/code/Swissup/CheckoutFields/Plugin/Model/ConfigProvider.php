<?php
namespace Swissup\CheckoutFields\Plugin\Model;

class ConfigProvider
{
    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;
    /**
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutFields\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return string
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if ($this->helper->isEnabled()) {
            $result['swissupCheckoutFieldsEnabled'] = true;
        }
        return $result;
    }
}
