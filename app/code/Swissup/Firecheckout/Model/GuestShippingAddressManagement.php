<?php

namespace Swissup\Firecheckout\Model;

class GuestShippingAddressManagement
    implements \Swissup\Firecheckout\Api\GuestShippingAddressManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Swissup\Firecheckout\Api\ShippingAddressManagementInterface
     */
    protected $shippingAddressManagement;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Swissup\Firecheckout\Api\ShippingAddressManagementInterface $shippingAddressManagement
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Swissup\Firecheckout\Api\ShippingAddressManagementInterface $shippingAddressManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shippingAddressManagement = $shippingAddressManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveShippingAddress(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingAddressManagement->saveShippingAddress(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }
}
