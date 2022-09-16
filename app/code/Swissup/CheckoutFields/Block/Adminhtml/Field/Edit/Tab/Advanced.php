<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Field\Edit\Tab;

class Advanced extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\Yesno $yesNo
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        array $data = []
    ) {
        $this->yesNo = $yesNo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Adding form elements for editing field
     *
     * @return $this
     * @SuppressWarnings(PHPMD)
     */
    protected function _prepareForm()
    {
        $fieldObject = $this->getFieldObject();

        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'advanced_fieldset',
            ['legend' => __('Advanced Field Properties')]
        );

        $yesno = $this->yesNo->toOptionArray();

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name' => 'is_active',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $yesno,
                'value' => 1
            ]
        );

        $fieldset->addField(
            'is_used_in_grid',
            'select',
            [
                'name' => 'is_used_in_grid',
                'label' => __('Add to Column Options'),
                'title' => __('Add to Column Options'),
                'values' => $yesno,
                'note' => __('Select "Yes" to add this attribute to the list of column options in the orders grid.'),
            ]
        );

        $validateClass = sprintf(
            'validate-code validate-length maximum-length-%d',
            \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'note' => __(
                    'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                    \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH
                ),
                'class' => $validateClass
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'note' => __('Use values greater than 1'),
                'class' => 'validate-greater-than-zero',
                'value' => 10
            ]
        );

        $fieldset->addField(
            'notice',
            'text',
            [
                'name' => 'notice',
                'label' => __('Notice'),
                'title' => __('Notice'),
                'note' => __('An explanation for the customer displayed under the field on the checkout page')
            ]
        );

        $fieldset->addField(
            'tooltip',
            'text',
            [
                'name' => 'tooltip',
                'label' => __('Tooltip'),
                'title' => __('Tooltip'),
                'note' => __('The short description displayed in the tooltip for the field')
            ]
        );

        $fieldset->addField(
            'placeholder',
            'text',
            [
                'name' => 'placeholder',
                'label' => __('Placeholder'),
                'title' => __('Placeholder'),
                'note' => __('The short hint displayed in the text fields before the user enters a value')
            ]
        );

        $fieldset->addField(
            'default_value_text',
            'text',
            [
                'name' => 'default_value_text',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue()
            ]
        );

        $fieldset->addField(
            'default_value_yesno',
            'select',
            [
                'name' => 'default_value_yesno',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'values' => $yesno,
                'value' => $fieldObject->getDefaultValue()
            ]
        );

        $dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField(
            'default_value_date',
            'date',
            [
                'name' => 'default_value_date',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue(),
                'date_format' => $dateFormat
            ]
        );

        $fieldset->addField(
            'default_value_textarea',
            'textarea',
            [
                'name' => 'default_value_textarea',
                'label' => __('Default Value'),
                'title' => __('Default Value'),
                'value' => $fieldObject->getDefaultValue()
            ]
        );

        if ($fieldObject->getId()) {
            $form->getElement('attribute_code')->setDisabled(1);
        }

        $this->setForm($form);
        return $this;
    }

    /**
     * Initialize form fileds values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getFieldObject()->getData());
        return parent::_initFormValues();
    }

    /**
     * Retrieve field object from registry
     *
     * @return mixed
     */
    private function getFieldObject()
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
