<?php

namespace BexioSync\BexioSync\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class DataSync extends AbstractHelper
{

    public function isEnabled(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');

        // check module enabled / not
        $sectionId = 'config';
        $groupId = 'settings';
        $fieldId = 'enable';
        $configPath = $sectionId . '/' . $groupId . '/' . $fieldId;
        $value =  $scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($value) {
            return true;
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param [type] $data array of customer data
     * @return void
     */
    public function createUser($customerData)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);

        // create customer on bexio
        $curl2 = curl_init();

        curl_setopt_array($curl2, array(
            CURLOPT_URL => 'https://api.bexio.com/3.0/fictional_users',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($customerData),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA',
                'Content-Type: application/json'
            ),
        ));

        $response2 = curl_exec($curl2);
        $customerRes = json_decode($response2);

        if(isset($customerRes->error_code) && $customerRes->error_code != '' && $customerRes->error_code == 422){
            $logger->error("User already exist.");
        }else{
            $logger->info("Customer successfully created ".$customerData['email']);
        }

        if (isset($customerRes->id) && !empty($customerRes->id)) {
            // load customer by id
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $tableName = $resource->getTableName('customer_entity'); //gives table name with 
            //Update Data into table
            $sql = "Update " . $tableName . " Set external_customer_id = " . $customerRes->id . " where email = '" . $customerData['email'] . "'";
            $sql2 = "Update " . $tableName . " Set external_customer_source ='Bexio' where email = '" . $customerData['email'] . "'";
            $connection->query($sql);
            $connection->query($sql2);
            
            return $customerRes->id;
        }
    }
}
