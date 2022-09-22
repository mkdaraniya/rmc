<?php

namespace Swissup\Taxvat\Plugin\Model;

use Magento\Framework\Exception\ValidatorException;
use Magento\Multishipping\Model\Checkout\Type\Multishipping\State;

class MultishippingState
{
    /**
     * @var \Swissup\Taxvat\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @param \Swissup\Taxvat\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     */
    public function __construct(
        \Swissup\Taxvat\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
    ) {
        $this->helper = $helper;
        $this->checkoutSession = $checkoutSession;
        $this->addressRepository = $addressRepository;
    }

    /**
     * @param State  $subject
     * @param string $step
     * @return void
     * @throws ValidatorException
     */
    public function beforeSetCompleteStep(
        State $subject,
        $step
    ) {
        if ($step === State::STEP_BILLING) {
            $this->validateBillingAddress();
        }
    }

    private function validateBillingAddress()
    {
        if (!$this->helper->canValidateVat()) {
            return;
        }

        $address = $this->checkoutSession->getQuote()->getBillingAddress();

        if ($addressId = $address->getCustomerAddressId()) {
            try {
                $address = $this->addressRepository->getById($addressId);
            } catch (\Exception $e) {
                return;
            }
        }

        if ($this->helper->validateAddress($address)) {
            return;
        }

        throw new ValidatorException(__('Please enter a valid VAT number.'));
    }
}
