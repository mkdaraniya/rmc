<?php

namespace Swissup\Firecheckout\Api;

/**
 * Interface for managing guest shipping address
 * @api
 */
interface GuestShippingAddressManagementInterface
{
    /**
     * @param string $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return \Magento\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function saveShippingAddress(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    );
}
