<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Field;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Admin resource
     */
    const ADMIN_RESOURCE = 'Swissup_CheckoutFields::checkoutfields';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_CheckoutFields::checkoutfields')
            ->addBreadcrumb(__('Checkout Fields'), __('Checkout Fields'))
            ->addBreadcrumb(__('Edit Field'), __('Edit Field'));
        return $resultPage;
    }

    /**
     * Edit field
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('field_id');
        $model = $this->_objectManager->create('Swissup\CheckoutFields\Model\Field');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This field no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFieldData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        $this->_coreRegistry->register('entity_attribute', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Field') : __('New Field'),
            $id ? __('Edit Field') : __('New Field')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Checkout Fields'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getFrontendLabel() : __('New Field'));

        return $resultPage;
    }
}
