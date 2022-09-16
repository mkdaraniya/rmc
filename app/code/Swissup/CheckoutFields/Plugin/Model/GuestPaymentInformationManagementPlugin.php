<?php
namespace Swissup\CheckoutFields\Plugin\Model;

class GuestPaymentInformationManagementPlugin extends AbstractPaymentInformationManagementPlugin
{
    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @param \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator,
        \Swissup\CheckoutFields\Helper\Data $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        parent::__construct(
            $fieldsValidator, $helper, $quoteRepository,
            $fieldValueFactory, $fieldFactory, $storeManager
        );
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
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
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Api\GuestPaymentInformationManagementInterface $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->helper->isEnabled()) {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $this->validateAndSaveFields($quoteIdMask->getQuoteId(), $paymentMethod);
        }
    }
}
