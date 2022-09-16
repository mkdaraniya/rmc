<?php
namespace Swissup\CheckoutFields\Plugin\Model;

class AdminOrderCreate
{
    /**
     * @var \Swissup\CheckoutFields\Model\Field\Validator
     */
    protected $fieldsValidator;

    /**
     * @var \Swissup\CheckoutFields\Model\FieldFactory
     */
    protected $fieldFactory;

    /**
     * @var \Swissup\CheckoutFields\Model\Field\ValueFactory
     */
    protected $fieldValueFactory;

    /**
     * @param \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator
     * @param \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory
     * @param \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
     */
    public function __construct(
        \Swissup\CheckoutFields\Model\Field\Validator $fieldsValidator,
        \Swissup\CheckoutFields\Model\FieldFactory $fieldFactory,
        \Swissup\CheckoutFields\Model\Field\ValueFactory $fieldValueFactory
    ) {
        $this->fieldsValidator = $fieldsValidator;
        $this->fieldFactory = $fieldFactory;
        $this->fieldValueFactory = $fieldValueFactory;
    }

    /**
     * Create new order
     *
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param \Magento\Sales\Model\Order $result
     * @return \Magento\Sales\Model\Order
     */
    public function afterCreateOrder(
        \Magento\Sales\Model\AdminOrder\Create $subject,
        \Magento\Sales\Model\Order $result
    ) {
        $fields = $subject->getData('swissup_checkout_fields');
        if (!empty($fields)) {
            if ($this->fieldsValidator->isValid($fields)) {
                foreach ($fields as $code => $value) {
                    if (empty($value) && !is_numeric($value)) {
                        continue;
                    }

                    $fieldId = $this->fieldFactory->create()->loadByCode($code)->getId();
                    $fieldValueModel = $this->fieldValueFactory->create();
                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                    $fieldValueModel
                        ->setFieldId($fieldId)
                        ->setStoreId($result->getStoreId())
                        ->setQuoteId($result->getQuoteId())
                        ->setOrderId($result->getId())
                        ->setValue($value)
                        ->save();
                }
            } else {
                throw new \Magento\Framework\Exception\CouldNotSaveException(
                    __('Please fill all required fields before placing the order.')
                );
            }
        }

        return $result;
    }
}
