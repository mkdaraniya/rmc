<?php

namespace Scrumwheel\Shippy\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    private $resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->get('\Magento\Framework\App\Request\Http');

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippy.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info("Shippy Webhook");

        $logger->info("request : ".json_encode($request->getParams()));

        $logger->info("webhook End ");

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData(['status' => true]);

    }
}
