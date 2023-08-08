<?php

namespace Scrumwheel\Shippy\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

class OrderObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippy_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Order Observer Call");


        // Get the order from the observer
        $order = $observer->getEvent()->getOrder();

        // Delayed execution after 5 minutes
        $this->scheduleShippyApi($order->getId());

        $logger->info("Order API Scheduled");
    }

    protected function scheduleShippyApi($orderId)
    {
        $objectManager = ObjectManager::getInstance();

        $cronHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Acme\StackExchange\Helper\Cron::class);

        // create task and schedule right now
        $cronHelper->create('scrumwheel_shippy_delayedshippyapi', null, ['key' => 'value']);


    }
}
