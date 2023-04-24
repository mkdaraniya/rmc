<?php

namespace BexioSync\BexioSync\Cron;


class OrderStatusCron
{

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $apiUri = 'https://bexio.free.beeceptor.com/2.0/kb_order';

        $dataHelper = $objectManager->create('\BexioSync\BexioSync\Helper\DataSync');
        $orderCollectionFactory = $objectManager->get('\Magento\Sales\Model\ResourceModel\Order\CollectionFactory');

        // check module enabled / not
        if (!$dataHelper->isEnabled()) {
            return true;
        }

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info(__METHOD__);
        $logger->info("Order Status Sync Cron Job Start");

        try {

            $orderList = $orderCollectionFactory->create()
                ->addAttributeToSelect('*');

            foreach ($orderList as $order) {
                $orderData = $order->getData();

                if (isset($orderData['external_order_id']) || $orderData['external_order_id'] != '') {

                    if ($orderData['status'] != 'completed') {
                        $curl = curl_init();

                        curl_setopt_array($curl, array(
                            CURLOPT_URL => 'https://api.bexio.com/2.0/kb_order/'.$orderData['external_order_id'],
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => '',
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => 'GET',
                            CURLOPT_HTTPHEADER => array(
                                'Accept: application/json',
                                'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA'
                            ),
                        ));

                        $response = curl_exec($curl);
                        curl_close($curl);
                        $responseData = json_decode($response);

                        if (isset($responseData->kb_item_status_id)) {
                            $order2 = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getEntityId());
                            if ($responseData->kb_item_status_id == 5) {
                                $order2->setStatus("pending");
                            } elseif ($responseData->kb_item_status_id == 6) {
                                $order2->setStatus("complete");
                            } elseif ($responseData->kb_item_status_id == 5) {
                                $order2->setStatus("processing");
                            }
                            $order2->save();

                            $logger->info("Order Status Updated Order Id : " . $order->getEntityId());
                        } else {
                            $logger->error("Order status not updated Order Id: " . $orderData['entity_id']);
                        }
                    }
                }
            }

            $logger->info("Order Status Sync Cron Job End");

            return $this;
        } catch (\Exception $e) {
            return $logger->critical($e);
        }
        return true;
    }
}
