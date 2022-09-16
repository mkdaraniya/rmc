<?php
namespace Swissup\CheckoutFields\Controller\Adminhtml\Field;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Swissup_CheckoutFields::checkoutfields';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Swissup_CheckoutFields::checkoutfields');
        $resultPage->addBreadcrumb(__('Checkout Fields'), __('Checkout Fields'));
        $resultPage->addBreadcrumb(__('Manage Fields'), __('Manage Fields'));
        $resultPage->getConfig()->getTitle()->prepend(__('Checkout Fields'));

        return $resultPage;
    }
}
