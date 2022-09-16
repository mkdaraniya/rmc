<?php

namespace Swissup\Firecheckout\Plugin\Model;

class ShippingMethodManagement
{
    /**
     * @var \Swissup\Firecheckout\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\Firecheckout\Helper\Config\Shipping
     */
    private $shippingHelper;

    /**
     * @param \Swissup\Firecheckout\Helper\Data $helper
     * @param \Swissup\Firecheckout\Helper\Config\Shipping $shippingHelper
     */
    public function __construct(
        \Swissup\Firecheckout\Helper\Data $helper,
        \Swissup\Firecheckout\Helper\Config\Shipping $shippingHelper
    ) {
        $this->helper = $helper;
        $this->shippingHelper = $shippingHelper;
    }

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $subject
     * @param array $rates
     * @return array
     */
    public function afterEstimateByAddress(
        \Magento\Quote\Api\ShippingMethodManagementInterface $subject,
        $rates
    ) {
        return $this->sort($rates);
    }

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $subject
     * @param array $rates
     * @return array
     */
    public function afterEstimateByExtendedAddress(
        \Magento\Quote\Api\ShippingMethodManagementInterface $subject,
        $rates
    ) {
        return $this->sort($rates);
    }

    /**
     * @param \Magento\Quote\Api\ShippingMethodManagementInterface $subject
     * @param array $rates
     * @return array
     */
    public function afterEstimateByAddressId(
        \Magento\Quote\Api\ShippingMethodManagementInterface $subject,
        $rates
    ) {
        return $this->sort($rates);
    }

    /**
     * @param array $rates
     * @return array
     */
    private function sort($rates)
    {
        if (!$this->helper->isFirecheckoutEnabled() ||
            !$this->shippingHelper->getSortShippingMethodsByPrice()
        ) {
            return $rates;
        }

        usort($rates, function ($a, $b) {
            return $a->getAmount() <=> $b->getAmount();
        });

        return $rates;
    }
}
