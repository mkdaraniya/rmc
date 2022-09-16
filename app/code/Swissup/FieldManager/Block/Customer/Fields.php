<?php
namespace Swissup\FieldManager\Block\Customer;

use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Framework\App\ProductMetadataInterface;

abstract class Fields extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    private $yesnoFactory;

    /**
     * @var \Swissup\FieldManager\Helper\Data
     */
    private $helper;

    protected $customerData = null;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Constructor
     *
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Swissup\FieldManager\Helper\Data $helper
     * @param ProductMetadataInterface $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Swissup\FieldManager\Helper\Data $helper,
        ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->jsonEncoder = $jsonEncoder;
        $this->yesnoFactory = $yesnoFactory;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve serialized JS layout configuration ready to use in template
     *
     * @return string
     */
    public function getJsLayout()
    {
        $this->prepareJsLayout();

        return parent::getJsLayout();
    }

    private function prepareJsLayout()
    {
        $fields = $this->attributeMetadataDataProvider->loadAttributesCollection(
            static::ENTITY_TYPE,
            $this->getData('formCode')
        );

        $config = [];
        $storeId = $this->_storeManager->getStore()->getId();
        foreach ($fields as $field) {
            if (!$this->canDisplayField($field)) {
                continue;
            }

            $config[$field->getAttributeCode()] = [
                'label' => $field->getStoreLabel($storeId),
                'config' => [
                    'customScope' => 'custom_attributes',
                    'template' => 'ui/form/field',
                    'elementTmpl' => $this->helper->getFieldTemplate($field),
                    'multiple' => $field->getFrontendInput() == 'multiselect',
                    'additionalClasses' => $this->getAdditionalClasses($field)
                ],
                'validation' => $this->getFieldValidation($field),
                'options' => $this->getFieldOptions($field),
                'component' => $this->helper->getFieldUiComponent($field),
                'provider' => 'swissup' . ucfirst(static::ENTITY_TYPE) . 'Provider',
                'caption' => __('Please select'),
                'dataScope' => $this->getFieldDataScope($field),
                'sortOrder' => $field->getSortOrder(),
                'visible' => $field->getIsVisible() ? true : false,
                'value' => $this->getFieldValue($field)
            ];
        }

        $this->jsLayout['components'][$this->getNameInLayout()]['children'] = $config;
    }

    /**
     * Get field dataScope
     * @param  $field
     * @return string
     */
    protected function getFieldDataScope($field)
    {
        $scope = 'custom_attributes.' . $field->getAttributeCode();

        if ($field->getFrontendInput() == 'multiselect') {
            $scope .= '[]';
        }

        return $scope;
    }

    /**
     * Check if field can be displayed
     * @param  $field
     * @return boolean
     */
    protected function canDisplayField($field)
    {
        return $field->getIsUserDefined() &&
               $this->helper->getFieldTemplate($field) &&
               !$this->helper->isFieldIgnored($field, static::ENTITY_TYPE);
    }

    /**
     * @param  $field
     * @return string
     */
    protected function getAdditionalClasses($field)
    {
        $classes = '';
        if ($field->getIsRequired() == 1) {
            $classes = 'required';
        }

        if ($field->getFrontendInput() == 'multiselect') {
            $classes .= ' multiple';
        }

        return trim($classes);
    }

    /**
     * @param  $field
     * @return string|null
     */
    protected function getFieldValue($field)
    {
        $value = null;
        if ($this->getCustomerData()) {
            $fieldValueObject = $this->customerData
                ->getCustomAttribute($field->getAttributeCode());
            $value = $fieldValueObject ? $fieldValueObject->getValue() : null;
        }

        if ($value === null) {
            $value = $field->getDefaultValue();
            if ($field->getFrontendInput() == 'multiselect' && $value) {
                $value = explode(',', $value);
            }
        }

        if ($value && $field->getFrontendInput() == 'date') {
            $value = $this->_localeDate->formatDate($value);
        }

        return $value;
    }

    protected function getCustomerData()
    {
        return null;
    }

    /**
     * @param  $field
     * @return array
     */
    private function getFieldValidation($field)
    {
        $validation = [];
        if ($field->getIsRequired() == 1) {
            if ($field->getFrontendInput() == 'multiselect') {
                $validation['validate-one-required'] = true;
            }
        }

        return $validation;
    }

    /**
     * @param  $field
     * @param  int $storeId
     * @return array
     */
    protected function getFieldOptions($field)
    {
        if ($field->getFrontendInput() == 'date') {
            $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
            $options = [
                'dateFormat' => $dateFormat
            ];
        } elseif ($field->getFrontendInput() == 'boolean') {
            $options = $this->yesnoFactory->create()->toOptionArray();
        } else {
            $options = [];
            $optionsArr = $field->getOptions();
            foreach ($optionsArr as $option) {
                $options[] = [
                    'value' => $option->getValue(),
                    'label' => $option->getLabel()
                ];
            }
        }

        return $options;
    }

    /**
     * Show block only for Magento Community
     * @return bool
     */
    public function canShow()
    {
        return $this->productMetadata->getEdition() == 'Community';
    }
}
