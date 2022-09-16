<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Order\Create;

class Form extends \Magento\Sales\Block\Adminhtml\Order\Create\Form\AbstractForm
{
    /**
     * Checkout fields collection factory
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory
     */
    protected $fieldsCollectionFactory;

    /**
     * Field values collection factory
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory
     */
    public $fieldValueCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsCollectionFactory
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory $fieldValueCollectionFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsCollectionFactory,
        \Swissup\CheckoutFields\Model\ResourceModel\Field\Value\CollectionFactory $fieldValueCollectionFactory,
        array $data = []
    ) {
        $this->fieldsCollectionFactory = $fieldsCollectionFactory;
        $this->fieldValueCollectionFactory = $fieldValueCollectionFactory;
        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $data
        );
    }

    /**
     * Return Header CSS Class
     *
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-swissup-checkout-fields';
    }

    /**
     * Return header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Additional Information');
    }

    protected function _prepareForm()
    {
        $fieldsCollection = $this->fieldsCollectionFactory->create()
            ->addStoreFilter($this->getStoreId())
            ->addIsActiveFilter(1)
            ->addOrder(
                \Swissup\CheckoutFields\Api\Data\FieldInterface::SORT_ORDER,
                \Magento\Framework\Data\Collection::SORT_ORDER_ASC
            );

        $fields = [];
        foreach ($fieldsCollection as $field) {
            $fields[$field->getAttributeCode()] = $field;
        }

        $fieldset = $this->_form->addFieldset('main', []);
        $this->_addAttributesToForm($fields, $fieldset);
        $this->_form->addFieldNameSuffix('order[swissup_checkout_fields]');

        $formValues = $this->getFieldValues($fields);
        foreach ($fields as $code => $field) {
            $defaultValue = $field->getDefaultValue();
            if (isset($defaultValue) && !isset($formValues[$code])) {
                $formValues[$code] = $defaultValue;
            }
        }
        $this->_form->setValues($formValues);

        return $this;
    }

    /**
     * Add rendering checkout fields to Form element
     *
     * @param \Swissup\CheckoutFields\Model\Field[] $fields
     * @param \Magento\Framework\Data\Form\AbstractForm $form
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _addAttributesToForm($fields, \Magento\Framework\Data\Form\AbstractForm $form)
    {
        // add additional form types
        $types = $this->_getAdditionalFormElementTypes();
        foreach ($types as $type => $className) {
            $form->addType($type, $className);
        }

        foreach ($fields as $field) {
            $inputType = $field->getFrontendInput();

            if ($inputType) {
                $element = $form->addField(
                    $field->getAttributeCode(),
                    $inputType,
                    [
                        'name' => $field->getAttributeCode(),
                        'label' => __($field->getStoreLabel($this->getStoreId())),
                        'class' => $field->getFrontendClass(),
                        'required' => $field->getIsRequired()
                    ]
                );
                $element->setEntityAttribute($field);

                if ($inputType == 'select' || $inputType == 'multiselect') {
                    $element->setValues($field->getSource()->getAllOptions());
                } elseif ($inputType == 'date') {
                    $format = $this->_localeDate->getDateFormat(
                        \IntlDateFormatter::SHORT
                    );
                    $element->setDateFormat($format);
                }
            }
        }

        return $this;
    }

    /**
     * Get checkout fields values from edited order
     * @param  array $fields
     * @return array
     */
    protected function getFieldValues($fields)
    {
        $values = [];
        if ($order = $this->_getSession()->getOrder()) {
            $items = $this->fieldValueCollectionFactory->create()
                ->addEmptyValueFilter()
                ->addStoreFilter($order->getStoreId())
                ->addOrderFilter($order->getId())
                ->getItems();

            foreach ($fields as $code => $field) {
                foreach ($items as $item) {
                    if ($field->getId() == $item->getFieldId()) {
                        $values[$code] = $item->getValue();
                        continue;
                    }
                }

            }
        }

        return $values;
    }
}
