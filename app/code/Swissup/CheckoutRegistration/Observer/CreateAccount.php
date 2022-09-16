<?php

namespace Swissup\CheckoutRegistration\Observer;

use Magento\Store\Model\ScopeInterface;
use Swissup\CheckoutRegistration\Model\System\Config\Source\RegistrationMode;

class CreateAccount implements \Magento\Framework\Event\ObserverInterface
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
     * @var \Magento\Sales\Api\OrderCustomerManagementInterface
     */
    private $orderCustomerService;

    /**
     * @var \Magento\Quote\Model\Quote\AddressFactory
     */
    private $quoteAddressFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Swissup\CheckoutRegistration\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Swissup\CheckoutRegistration\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Api\OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Psr\Log\LoggerInterface $logger,
        \Swissup\CheckoutRegistration\Helper\Data $helper
    ) {
        $this->accountManagement = $accountManagement;
        $this->customerSession = $customerSession;
        $this->orderCustomerService = $orderCustomerService;
        $this->quoteAddressFactory = $quoteAddressFactory;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     * Trigger account creation.
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->helper->isRegistrationAllowed() || $this->customerSession->isLoggedIn()) {
            return;
        }

        if (!$this->customerSession->getRegistrationPasswordHash() &&
            !$this->helper->isRegistrationAutomatic()
        ) {
            return;
        }

        $order = $observer->getOrder();

        if (!$this->accountManagement->isEmailAvailable($order->getCustomerEmail())) {
            return;
        }

        try {
            $this->prepareOrderAddresses($order);

            $customer = $this->orderCustomerService->create($order->getId());

            // if automatic - customer must set password before login using the link from email,
            // otherwise, he will not be able to edit an account.
            if (!$this->helper->isRegistrationAutomatic() &&
                !$this->isConfirmationRequired()
            ) {
                $this->customerSession->setCustomerDataAsLoggedIn($customer);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * Prepare addresses to be linked with customer account
     *
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    private function prepareOrderAddresses($order)
    {
        $addressIds = [];

        $billing = $order->getBillingAddress();
        if ($billing) {
            $addressIds[] = $billing->getQuoteAddressId();
        }

        $shipping = $order->getShippingAddress();
        if ($shipping) {
            if (!$billing ||
                $shipping->getStreet() !== $billing->getStreet() ||
                $shipping->getCity() !== $billing->getCity() ||
                $shipping->getFirstname() !== $billing->getFirstname() ||
                $shipping->getLastname() !== $billing->getLastname()
            ) {
                $addressIds[] = $shipping->getQuoteAddressId();
            }
        }

        foreach ($addressIds as $adressId) {
            $address = $this->quoteAddressFactory->create()->load($adressId);
            if ($address && $address->getId()) {
                try {
                    $address->setData('save_in_address_book', 1)->save();
                } catch (\Exception $e) {
                    //
                }
            }
        }
    }

    /**
     * \Magento\Customer\Model\AccountConfirmation is not used to not to break 2.2.3
     *
     * @return boolean
     */
    private function isConfirmationRequired()
    {
        return (bool) $this->helper->getConfigValue(
            'customer/create_account/confirm',
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
