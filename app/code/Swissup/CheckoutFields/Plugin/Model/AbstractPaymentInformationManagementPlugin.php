<?php
namespace Swissup\CheckoutFields\Plugin\Model;

class AbstractPaymentInformationManagementPlugin
{
    /**
     * @var \Swissup\CheckoutFields\Model\Field\Validator
     */
    protected $fieldsValidator;

    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;

    /**
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Swissup\CheckoutFields\Model\Field\ValueFactory
     */
    protected $fieldValueFactory;

    /**
     * @var \Swissup\CheckoutFields\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * Store Manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator,
        \Swissup\CheckoutFields\Helper\Data $helper,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->fieldsValidator = $fieldsValidator;
        $this->helper = $helper;
        $this->quoteRepository = $quoteRepository;
        $this->fieldValueFactory = $fieldValueFactory;
        $this->fieldFactory = $fieldFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return void
     */
    protected function validateAndSaveFields(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    )
    {
        $fields = $paymentMethod->getExtensionAttributes() === null
            ? []
            : $paymentMethod->getExtensionAttributes()->getSwissupCheckoutFields();

        if (!empty($fields)) {
            if ($this->fieldsValidator->isValid($fields)) {
                $quote = $this->quoteRepository->getActive($cartId);
                $quote->setSwissupCheckoutFields($fields);

                $storeId = $this->storeManager->getStore()->getId();
                foreach ($fields as $code => $value) {
                    if (empty($value) && !is_numeric($value)) {
                        continue;
                    }

                    $fieldId = $this->fieldFactory->create()->loadByCode($code)->getId();

                    $fieldValueModel = $this->fieldValueFactory->create()
                        ->loadByQuoteFieldAndStore($quote->getId(), $fieldId, $storeId);

                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $fieldValueModel->setValue($value)->save();
                }
            } else {
                throw new \Magento\Framework\Exception\CouldNotSaveException(
                    __('Please fill all required fields before placing the order.')
                );
            }
        }
    }
}
