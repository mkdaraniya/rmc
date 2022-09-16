<?php
namespace Swissup\CheckoutCart\Plugin\Model;

class ConfigProvider
{
    /**
     * @var \Swissup\CheckoutCart\Helper\Data
     */
    protected $helper;
    /**
     * @param \Swissup\CheckoutCart\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutCart\Helper\Data $helper
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
        $result['swissup']['CheckoutCart'] = [
            'enabled' => $this->helper->isEnabled(),
            'productLinkEnabled' => $this->helper->productLinkEnabled(),
            'productSkuEnabled' => $this->helper->productSkuEnabled(),
            'qtyIncrementEnabled' => $this->helper->qtyIncrementEnabled(),
        ];

        return $result;
    }
}
