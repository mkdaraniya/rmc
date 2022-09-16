<?php

namespace Swissup\Firecheckout\Api;

/**
 * Interface for managing customer shipping method
 * @api
 */
interface ShippingMethodManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveShippingMethod(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
