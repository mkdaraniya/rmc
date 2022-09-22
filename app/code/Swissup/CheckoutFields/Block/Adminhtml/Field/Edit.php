<?php
namespace Swissup\CheckoutFields\Block\Adminhtml\Field;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize field edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'field_id';
        $this->_blockGroup = 'Swissup_CheckoutFields';
        $this->_controller = 'adminhtml_field';

        parent::_construct();

        if ($this->_isAllowedAction('Swissup_CheckoutFields::save')) {
            $this->buttonList->update('save', 'label', __('Save Field'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Swissup_CheckoutFields::delete')) {
            $this->buttonList->update('delete', 'label', __('Delete Field'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $field = $this->_coreRegistry->registry('entity_attribute');
        if ($field->getId()) {
            return __("Edit Field '%1'", $this->escapeHtml($field->getFrontendLabel()));
        } else {
            return __('New Field');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }

    /**
     * Retrieve URL for validation
     *
     * @return string
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', ['_current' => true]);
    }
}
