<?php

namespace BexioSync\BexioSync\Observer;

class AddCustomerData implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getData('customer');

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
        $logger->info("Customer Observer");


    }
}
