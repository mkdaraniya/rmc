<?php

namespace Swissup\Firecheckout\Api;

/**
 * Interface for managing guest shipping method
 * @api
 */
interface GuestShippingMethodManagementInterface
{
    /**
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveShippingMethod(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
