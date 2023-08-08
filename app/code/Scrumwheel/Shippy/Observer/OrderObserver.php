<?php

namespace Scrumwheel\Shippy\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;

class OrderObserver implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippy_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Order Observer Call");

        // Get the order from the observer
        $order = $observer->getEvent()->getOrder();

        $shipping = $order->getShippingAddress()->getData();

        $params = [

            'Method' => 'Ship',
            'Params' => [
                'to_address' => [
                    "name" => $shipping['firstname'] . ' ' . $shipping['lastname'],
                    "company" => $shipping['company'],
                    "street1" => $shipping['street'],
                    "street2" => "",
                    "city" => $shipping['city'],
                    "state" => $shipping['region'],
                    "zip" => $shipping['postcode'],
                    "country" => $shipping['country_id'],
                    "phone" => $shipping['telephone'],
                    "email" => $shipping['email']
                ],
                'from_address' => [
                    "name" => "Kalpana Ekambaranathan",
                    "company" => "SWANT AG",
                    "street1" => "Pestalozzistrasse  16",
                    "street2" => "",
                    "city" => "Burgdorf",
                    "state" => "",
                    "zip" => "3400",
                    "country" => "CH",
                    "phone" => "+41797810411",
                    "email" => "info@swant.com"
                ],
                "parcels" => [
                    [
                        "length" => 5,
                        "width" => 5,
                        "height" => 5,
                        "weight" => 10
                    ]
                ],
                "TotalValue" => $order->getData('increment_id'),
                "TransactionID" => $order->getData('increment_id'),
                "ContentDescription" => "Shoes",
                "Insurance" => 0,
                "InsuranceCurrency" => "EUR",
                "CashOnDelivery" => 0,
                "CashOnDeliveryCurrency" => $order->getData('order_currency_code'),
                "CashOnDeliveryType" => 0,
                "CarrierName" => "FEDEX",
                "CarrierService" => "International Economy",
                "CarrierID" => 5035,
                "OrderID" => $order->getData('increment_id'),
                "RateID" => "",
                "Incoterm" => "EXW",
                "BillAccountNumber" => "",
                "Note" => "Ship created using API",
                "Async" => false

            ]
        ];

        if ($order->getId()) {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.shippypro.com/api',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($params),
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json',
                    'Authorization: Basic NTExN2Q5MzNhODlmNWQ1ZjY4YzRiNjgxZDA3YWM3MmE6',
                    'Cookie: SSESS428f6684ce98abd418e683e3a25028bd=KKSD68rhMqh3vSpdEA3mhGIl2JzY_WNrx4Y8z6s2vWM'
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
            $order->addStatusHistoryComment($response);
            $order->save();
        }

        $logger->info("Order API Scheduled");
    }

    protected function scheduleShippyApi($orderId)
    {
        // $objectManager = ObjectManager::getInstance();
        // $cronHelper = \Magento\Framework\App\ObjectManager::getInstance()->get(\Acme\StackExchange\Helper\Cron::class);
        // // create task and schedule right now
        // $cronHelper->create('scrumwheel_shippy_delayedshippyapi', null, ['key' => 'value']);
    }
}
