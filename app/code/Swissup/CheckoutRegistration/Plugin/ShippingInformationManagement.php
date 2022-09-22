<?php

namespace Swissup\CheckoutRegistration\Plugin;

class ShippingInformationManagement
{
    /**
     * @var \Swissup\CheckoutRegistration\Model\RequestProcessor
     */
    public $requestProcessor;

    /**
     * @param \Swissup\CheckoutRegistration\Model\RequestProcessor $requestProcessor
     */
    public function __construct(
        \Swissup\CheckoutRegistration\Model\RequestProcessor $requestProcessor
    ) {
        $this->requestProcessor = $requestProcessor;
    }

    /**
     * Validate and save password hash to use during account creation.
     * @see \Swissup\CheckoutRegistration\Plugin\CustomerRepository
     *
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return void
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->requestProcessor->processShippingRequest($addressInformation);
    }
}
