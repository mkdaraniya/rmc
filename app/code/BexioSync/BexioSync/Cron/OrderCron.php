<?php

namespace BexioSync\BexioSync\Cron;

class OrderCron
{
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $apiUri = 'https://e1a5d7bf-2a7e-49ea-ad93-2a8223a900ca.mock.pstmn.io/';

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        $sectionId = 'config';
        $groupId = 'settings';
        $fieldId = 'enable';

        $configPath = $sectionId.'/'.$groupId.'/'.$fieldId;
        $value =  $scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if(!$value){
            return true;
        }
        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
        $logger->info("Order Sync Cron Job Start");

        try {

            $logger->info("Order Sync Cron Job End");

            return $this;
        } catch (\Exception $e) {
            return $logger->critical($e);
        }
        return true;
    }
}
