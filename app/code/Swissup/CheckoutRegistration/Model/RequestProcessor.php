<?php

namespace Swissup\CheckoutRegistration\Model;

use Magento\Framework\Exception\StateException;

class RequestProcessor
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swissup\CheckoutRegistration\Helper\Data
     */
    public $helper;

    /**
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Swissup\CheckoutRegistration\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\Session $customerSession,
        \Swissup\CheckoutRegistration\Helper\Data $helper
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * @return void
     */
    public function processShippingRequest(
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $this->process($addressInformation->getExtensionAttributes());
    }

    /**
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @return void
     */
    public function processPaymentRequest(
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $this->process($paymentMethod->getExtensionAttributes());
    }

    /**
     * @param mixed $attributes
     * @return void
     */
    private function process($attributes)
    {
        $this->customerSession->unsRegistrationPasswordHash();

        if ($this->customerSession->isLoggedIn()) {
            return;
        }

        if (!$this->helper->isRegistrationPasswordAllowed()) {
            return;
        }

        if (!$this->validateAttributes($attributes)) {
            return;
        }

        $this->customerSession->setRegistrationPasswordHash(
            $this->accountManagement->getPasswordHash(
                (string) $attributes->getRegistrationPassword()
            )
        );
    }

    /**
     * @param mixed $attributes
     * @return void
     * @throws \Exception
     */
    private function validateAttributes($attributes)
    {
        $isRegistrationRequired = $this->helper->isRegistrationRequired();

        if (!$attributes) {
            if ($isRegistrationRequired) {
                throw new StateException(__('%fieldName is a required field.', [
                    'fieldName' => __('Password'),
                ]));
            }
            return false;
        }

        if (!$isRegistrationRequired && !$attributes->getRegistrationCheckboxState()) {
            return false;
        }

        $password = (string) $attributes->getRegistrationPassword();
        if (!$password) {
            throw new StateException(__('%fieldName is a required field.', [
                'fieldName' => __('Password'),
            ]));
        }

        $passwordConfirmation = (string) $attributes->getRegistrationPasswordConfirmation();
        if (strcmp($password, $passwordConfirmation) !== 0) {
            throw new StateException(__("Password confirmation doesn't match entered password."));
        }

        return true;
    }
}
