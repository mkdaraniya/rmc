<?php

namespace Swissup\CheckoutRegistration\Plugin;

class CustomerRepository
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Swissup\CheckoutRegistration\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\CheckoutRegistration\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Swissup\CheckoutRegistration\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->helper = $helper;
    }

    /**
     * Inject password hash from the session if any.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string $passwordHash
     * @return mixed
     */
    public function beforeSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $passwordHash = null
    ) {
        if ($passwordHash ||
            !$this->helper->isRegistrationAllowed() ||
            $this->customerSession->isLoggedIn()
        ) {
            return;
        }

        $passwordHash = $this->customerSession->getRegistrationPasswordHash(true);

        if ($passwordHash || $this->helper->isRegistrationAutomatic()) {
            $customer->setData('ignore_validation_flag', true);
        }

        if (!$passwordHash) {
            return;
        }

        return [$customer, $passwordHash];
    }
}
