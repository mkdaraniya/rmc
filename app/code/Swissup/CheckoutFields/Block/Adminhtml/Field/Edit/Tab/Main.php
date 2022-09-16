<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Field\Edit\Tab;

use Swissup\CheckoutFields\Model\Field;

class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * List of supported input types
     */
    const ALLOWED_TYPES = [
        'text', 'textarea', 'date', 'boolean', 'multiselect', 'select'
    ];

    /**
     * Field instance
     *
     * @var Field
     */
    protected $field = null;

    /**
     * @var \Magento\Config\Model\Config\Source\YesnoFactory
     */
    protected $yesnoFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory
     */
    protected $inputTypeFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Swissup\CheckoutFields\Helper\Data $helper,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->yesnoFactory = $yesnoFactory;
        $this->inputTypeFactory = $inputTypeFactory;
        $this->helper = $helper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Set field object
     *
     * @param Field $field
     * @return $this
     * @codeCoverageIgnore
     */
    public function setFieldObject($field)
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Return field object
     *
     * @return Field
     */
    public function getFieldObject()
    {
        if (null === $this->field) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->field;
    }

    protected function _prepareForm()
    {
        $fieldObject = $this->getFieldObject();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Field Properties')]);

        if ($fieldObject->getFieldId()) {
            $fieldset->addField('field_id', 'hidden', ['name' => 'field_id']);
        }

        $this->_addElementTypes($fieldset);

        $yesno = $this->yesnoFactory->create()->toOptionArray();

        $labels = $fieldObject->getFrontendLabel();
        $fieldset->addField(
            'field_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true,
                'value' => is_array($labels) ? $labels[0] : $labels
            ]
        );

        /* Check is single store mode */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $fieldObject->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $inputTypes = $this->inputTypeFactory->create()->toOptionArray();
        $allowedTypes = array_filter($inputTypes, function ($var) {
            return in_array($var['value'], self::ALLOWED_TYPES);
        });
        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Catalog Input Type for Store Owner'),
                'title' => __('Catalog Input Type for Store Owner'),
                'value' => 'text',
                'values' => $allowedTypes
            ]
        );

        $frontendInput = $fieldObject->getFrontendInput() ?: self::ALLOWED_TYPES[0];
        $fieldset->addField(
            'display_type',
            'select',
            [
                'name' => 'display_type',
                'label' => __('Display Type'),
                'title' => __('Display Type'),
                'value' => 'default',
                'values' => $this->helper->getDisplayTypes($frontendInput)
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'values' => $yesno
            ]
        );

        if ($fieldObject->getId()) {
            $form->getElement('frontend_input')->setDisabled(1);
        }
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Initialize form fields values
     *
     * @return $this
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getFieldObject()->getData());
        return parent::_initFormValues();
    }
}
