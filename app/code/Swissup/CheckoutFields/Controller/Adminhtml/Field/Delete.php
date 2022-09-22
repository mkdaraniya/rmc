<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Field;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_CheckoutFields::delete';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry)
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('field_id');
        if ($id) {
            try {
                $model = $this->_objectManager->create('Swissup\CheckoutFields\Model\Field');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Checkout field was deleted.'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['field_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Can\'t find a field to delete.'));
        return $resultRedirect->setPath('*/*/');
    }
}
