<?php
namespace Swissup\Taxvat\Plugin\Model;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

class BillingAddressManagement
{
    /**
     * @var \Swissup\Taxvat\Helper\Data $helper
     */
    protected $helper;
    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After is not used for Magento 2.2 compatibility
     *
     * @param \Magento\Quote\Model\BillingAddressManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param boolean $useForShipping
     * @return mixed
     */
    public function aroundAssign(
        \Magento\Quote\Model\BillingAddressManagement $subject,
        callable $proceed,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address,
        $useForShipping = false
    ) {
        $result = $proceed($cartId, $address, $useForShipping);

        if (!$this->helper->canValidateVat()) {
            return $result;
        }

        if (!$this->helper->validateAddress($address)) {
            throw new ValidatorException(__('Please enter a valid VAT number.'));
        }

        return $result;
    }
}
