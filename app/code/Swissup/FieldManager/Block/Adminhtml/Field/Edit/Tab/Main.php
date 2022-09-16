<?php
namespace Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab;

class Main extends \Swissup\FieldManager\Block\Adminhtml\Field\Edit\Tab\AbstractTab
{
    /**
     * {@inheritdoc}
     * @return $this
     */
    protected function _prepareForm()
    {
        $fieldObject = $this->getFieldObject();
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Field Properties')]);
        if ($fieldObject->getAttributeId()) {
            $fieldset->addField('attribute_id', 'hidden', ['name' => 'attribute_id']);
        }
        $this->_addElementTypes($fieldset);
        $labels = $fieldObject->getFrontendLabel();

        $fieldset->addField(
            'field_label',
            'text',
            [
                'name' => 'frontend_label[0]',
                'label' => __('Default Label'),
                'title' => __('Default label'),
                'required' => true,
                'scope_label' => '[global]',
                'value' => is_array($labels) ? $labels[0] : $labels
            ]
        );

        $fieldset->addField(
            'frontend_input',
            'select',
            [
                'name' => 'frontend_input',
                'label' => __('Input Type'),
                'title' => __('Input Type'),
                'value' => 'text',
                'scope_label' => '[global]',
                'values' => $this->inputTypeFactory->create()->toOptionArray()
            ]
        );

        $fieldset->addField(
            'is_required',
            'select',
            [
                'name' => $fieldObject->getWebsite()->getId() ? 'scope_is_required' : 'is_required',
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'note' => __('Required field will be displayed on all forms'),
                'values' => $this->yesnoSource,
                'scope_label' => '[website]'
            ]
        )->setDisabled($fieldObject->getIsSystem())->setIsSystem($fieldObject->getIsSystem());

        if ($fieldObject->getId()) {
            $form->getElement('frontend_input')->setDisabled(1);
        }
        $this->setForm($form);
        $this->helper->addScopeHtml($this, ['is_required']);

        return parent::_prepareForm();
    }
}
