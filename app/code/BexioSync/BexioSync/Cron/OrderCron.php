<?php

namespace BexioSync\BexioSync\Cron;


class OrderCron
{

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $apiUri = 'https://bexio.free.beeceptor.com/2.0/kb_order';

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $orderCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

        $sectionId = 'config';
        $groupId = 'settings';
        $fieldId = 'enable';

        $configPath = $sectionId . '/' . $groupId . '/' . $fieldId;
        $value =  $scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$value) {
            return true;
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
        $logger->info("Order Sync Cron Job Start");

        try {

            $orderList = $orderCollectionFactory->create()
                ->addAttributeToSelect('*');

            foreach ($orderList as $order) {
                $orderData = $order->getData();

                if(!isset($orderData['external_order_id']) || $orderData['external_order_id'] == ''){
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => 'https://bexio.free.beeceptor.com/2.0/kb_order',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS => '{
                            "title": null,
                            "contact_id": 14,
                            "contact_sub_id": null,
                            "user_id": 1,
                            "pr_project_id": null,
                            "logopaper_id": 1,
                            "language_id": 1,
                            "bank_account_id": 1,
                            "currency_id": 1,
                            "payment_type_id": 1,
                            "header": "Thank you very much for your inquiry. We would be pleased to make you the following offer:",
                            "footer": "We hope that our offer meets your expectations and will be happy to answer your questions.",
                            "mwst_type": 0,
                            "mwst_is_net": true,
                            "show_position_taxes": false,
                            "is_valid_from": "2019-06-24",
                            "delivery_address_type": 0,
                            "api_reference": null,
                            "template_slug": "581a8010821e01426b8b456b",
                            "positions": [
                                {
                                "amount": "5.000000",
                                "unit_id": 1,
                                "account_id": 1,
                                "tax_id": 4,
                                "text": "Apples",
                                "unit_price": "3.560000",
                                "discount_in_percent": "0.000000",
                                "type": "KbPositionCustom",
                                "parent_id": null
                                }
                            ]
                            }',
                        CURLOPT_HTTPHEADER => array(
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);
                    curl_close($curl);

                    $responseData = json_decode($response);

                    if(isset($responseData->id)){
                        $order2 = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getEntityId());
                        $order2->setData('external_order_id',$responseData->id);
                        $order2->setData('external_order_source',"Bexio Api");
                        $order2->save();
                    }else{
                        $logger->info("Order not sync : ".$orderData['entity_id']);
                    }

                    $logger->info("Sync Order Id : ".$order->getEntityId());
                }
            }


            $logger->info("Order Sync Cron Job End");

            return $this;
        } catch (\Exception $e) {
            return $logger->critical($e);
        }
        return true;
    }
}
