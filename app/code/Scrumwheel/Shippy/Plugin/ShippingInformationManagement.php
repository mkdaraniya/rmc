<?php

namespace Scrumwheel\Shippy\Plugin;

use Magento\Quote\Api\CartRepositoryInterface;

class ShippingInformationManagement
{
    public $cartRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository
    ) {
        $this->cartRepository = $cartRepository;
    }

    public function beforeSaveAddressInformation($subject, $cartId, $addressInformation)
    {
        $quote = $this->cartRepository->getActive($cartId);
        $incoterm = $addressInformation->getShippingAddress()->getExtensionAttributes()->getIncoterm();
        $quote->setIncoterm($incoterm);
        $this->cartRepository->save($quote);
        return [$cartId, $addressInformation];
    }
}