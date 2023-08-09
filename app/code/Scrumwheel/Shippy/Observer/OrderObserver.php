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

        $orderId = $order->getData('entity_id');

        $this->scheduleShippyApi($orderId);

        $logger->info("Order API Scheduled");
    }

    protected function scheduleShippyApi($orderId)
    {
        $current_time = time();

        $delayTime = 10;
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippy_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        $logger->info('current_time : '. $current_time);
        $new_time = $current_time + $delayTime * 60;

        $logger->info("current time + 5min : ".$new_time);

        $cronHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Scrumwheel\Shippy\Helper\Cron::class);
        // create task and schedule right now
        $cronHelper->create('scrumwheel_shippy_delayedshippyapi', $new_time, ['order_id' => $orderId]);
    }
}
