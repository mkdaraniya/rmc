<?php

namespace Swissup\Taxvat\Plugin\Model;

class TaxSalesTotalQuoteCommonTaxCollector
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Copy entered vat number to the address. It will be validated
     * in Swissup\Taxvat\Plugin\Model\TaxCalculation then.
     *
     * @param \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Address $address
     * @return mixed
     */
    public function aroundMapAddress(
        \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote\Address $address
    ) {
        $customerAddress = $proceed($address);

        if (!$this->helper->canValidateVat() || !$this->helper->isTaxFreeEnabled()) {
            return $customerAddress;
        }

        if (!$customerAddress->getVatId() && $address->getVatId()) {
            $customerAddress->setVatId($address->getVatId());
        }

        return $customerAddress;
    }
}
