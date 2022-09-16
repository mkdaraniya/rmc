<?php

namespace Swissup\Firecheckout\Model;

class GuestShippingMethodManagement
    implements \Swissup\Firecheckout\Api\GuestShippingMethodManagementInterface
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Swissup\Firecheckout\Api\ShippingMethodManagementInterface
     */
    protected $shippingMethodManagement;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Swissup\Firecheckout\Api\ShippingMethodManagementInterface $shippingMethodManagement
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Swissup\Firecheckout\Api\ShippingMethodManagementInterface $shippingMethodManagement
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    /**
     * {@inheritDoc}
     */
    public function saveShippingMethod(
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->shippingMethodManagement->saveShippingMethod(
            $quoteIdMask->getQuoteId(),
            $addressInformation
        );
    }
}
