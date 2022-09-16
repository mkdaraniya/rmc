<?php

namespace Swissup\Firecheckout\Api;

/**
 * Interface for managing customer shipping address
 * @api
 */
interface ShippingAddressManagementInterface
{
    /**
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveShippingAddress(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
