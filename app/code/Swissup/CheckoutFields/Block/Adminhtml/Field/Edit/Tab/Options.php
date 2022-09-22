<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Field\Edit\Tab;

class Options extends \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options
{
    /**
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory
     */
    protected $fieldOptionCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory $fieldOptionCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\CollectionFactory $fieldOptionCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $attrOptionCollectionFactory, $universalFactory, $data);
        $this->_attrOptionCollectionFactory = $fieldOptionCollectionFactory;
        $this->fieldOptionCollectionFactory = $fieldOptionCollectionFactory;
    }

    /**
     * Retrieve field option values if field input type select or multiselect
     *
     * @return array
     */
    public function getOptionValues()
    {
        $values = $this->_getData('option_values');
        if ($values === null) {
            $values = [];

            $field = $this->getAttributeObject();
            $optionCollection = $this->fieldOptionCollectionFactory->create()
                ->setAttributeFilter($field->getId())
                ->setPositionOrder('asc', true)
                ->load();

            if ($optionCollection) {
                $values = $this->prepareOptionValues($field, $optionCollection);
            }

            $this->setData('option_values', $values);
        }

        return $values;
    }

    /**
     * @param \Swissup\CheckoutFields\Model\Field $field
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\Option\Collection $optionCollection
     * @return array
     */
    protected function prepareOptionValues($field, $optionCollection)
    {
        $type = $field->getFrontendInput();
        if ($type === 'select' || $type === 'multiselect') {
            $defaultValues = explode(',', (string) $field->getDefaultValue());
            $inputType = $type === 'select' ? 'radio' : 'checkbox';
        } else {
            $defaultValues = [];
            $inputType = '';
        }

        $values = [];
        foreach ($optionCollection as $option) {
            $bunch = $this->_prepareUserDefinedAttributeOptionValues(
                $option,
                $inputType,
                $defaultValues
            );
            foreach ($bunch as $value) {
                $values[] = new \Magento\Framework\DataObject($value);
            }
        }

        return $values;
    }
}
