<?php

namespace Swissup\CheckoutRegistration\Plugin;

class PaymentInformationManagementForGuests
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var \Swissup\CheckoutRegistration\Model\RequestProcessor
     */
    private $requestProcessor;

    /**
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepository
     * @param \Swissup\CheckoutRegistration\Model\RequestProcessor $requestProcessor
     */
    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Swissup\CheckoutRegistration\Model\RequestProcessor $requestProcessor
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->cartRepository = $cartRepository;
        $this->requestProcessor = $requestProcessor;
    }

    /**
     * @param \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject
     * @param int $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        $quote = $this->cartRepository->getActive($quoteIdMask->getQuoteId());

        if (!$quote->isVirtual()) {
            return;
        }

        $this->requestProcessor->processPaymentRequest($paymentMethod);
    }
}
