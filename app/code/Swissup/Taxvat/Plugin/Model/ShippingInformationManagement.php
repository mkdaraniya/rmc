<?php
namespace Swissup\Taxvat\Plugin\Model;

use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\RemoteServiceUnavailableException;

class ShippingInformationManagement
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
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param callable $proceed
     * @param int $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return mixed
     */
    public function aroundSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        callable $proceed,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $result = $proceed($cartId, $addressInformation);

        if (!$this->helper->canValidateVat()) {
            return $result;
        }

        $address = $addressInformation->getShippingAddress();
        if (!$this->helper->validateAddress($address)) {
            throw new ValidatorException(__('Please enter a valid VAT number.'));
        }

        return $result;
    }
}
