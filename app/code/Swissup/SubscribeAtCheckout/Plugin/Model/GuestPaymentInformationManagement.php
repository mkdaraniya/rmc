<?php

namespace Swissup\SubscribeAtCheckout\Plugin\Model;

class GuestPaymentInformationManagement
{
    /**
     * @var \Swissup\SubscribeAtCheckout\Api\SubscriberInterface
     */
    protected $subscriber;

    /**
     * @param \Swissup\SubscribeAtCheckout\Api\SubscriberInterface $subscriber
     */
    public function __construct(
        \Swissup\SubscribeAtCheckout\Api\SubscriberInterface $subscriber
    ) {
        $this->subscriber = $subscriber;
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($paymentMethod->getExtensionAttributes() &&
            $paymentMethod->getExtensionAttributes()->getSwissupSubscribeAtCheckout()
        ) {
            $this->subscriber->subscribe($email);
        }
    }
}
