<?php

namespace Scrumwheel\Shippy\Cron;

use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Serialize\SerializerInterface;

class DelayedShippyApi
{
    protected $logger;
    protected SerializerInterface $serializer;

    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer
    ) {
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    public function execute(Schedule $schedule)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $invoiceService = $objectManager->create('Magento\Sales\Model\Service\InvoiceService');
        $transaction = $objectManager->create('Magento\Framework\DB\Transaction');

        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shippy_api.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info("Order Cron Call");

        $arguments = [];
        if ($schedule->getArguments()) {
            $arguments = $this->serializer->unserialize($schedule->getArguments());
        }

        if (!empty($arguments) && isset($arguments['order_id'])) {
            $orderId = $arguments['order_id'];
            $order = $objectManager->create('Magento\Sales\Model\Order')->load($orderId);
            $shipping = $order->getShippingAddress()->getData();


            $CarrierName = 'FEDEX';
            $CarrierService = 'Standard';
            $CarrierID = 5035;
            if ($order->getShippingMethod() == 'tablerate_bestway' || $order->getShippingMethod() == '') {
                $CarrierName = 'MyDHL';
                $CarrierService = 'International Economy';
                $CarrierID = 6078;
            }

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
                    "CarrierName" => $CarrierName,
                    "CarrierService" => $CarrierService,
                    "CarrierID" => $CarrierID,
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
                $order->addStatusHistoryComment(
                    'Shippypro api called  ' .
                        'API endpoint - https://www.shippypro.com/api/  ' .
                        'Request - ' . json_encode($params) . '  ' .
                        $response
                );
                $order->save();

                $logger->info('Order comment saved');

                // upload pdf code
                $fileSystem = $objectManager->create('Magento\Framework\Filesystem');
                $varDirectory = $fileSystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
                $fileName = $order->getIncrementId() . '.pdf';
                $filePath = $varDirectory->getAbsolutePath($fileName);
                $shipping = $order->getShippingAddress()->getData();

                $fileContents = '';
                if ($varDirectory->isExist($fileName)) {
                    $fileContents = base64_encode(file_get_contents($filePath));
                }

                $curl = curl_init();

                $custLastName = $order->getCustomerLastname();
                $custFirsrName = $order->getCustomerFirstname();
                $customerName = $custFirsrName . ' ' . $custLastName;

                $postParam = [
                    "Method" => "UploadPaperlessDocumentation",
                    "Params" => [
                        "TransactionID" => $order->getIncrementId(),
                        "Name" => $customerName,
                        "Country" => $shipping['country_id'],
                        "Document" => $fileContents,
                        "DocumentType" => 5
                    ]
                ];

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://www.shippypro.com/api/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($postParam),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic NTExN2Q5MzNhODlmNWQ1ZjY4YzRiNjgxZDA3YWM3MmE6',
                        'Content-Type: application/json',
                    ),
                ));

                $response2 = curl_exec($curl);

                curl_close($curl);

                $order = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                $order->addStatusHistoryComment(
                    'Shippypro api called  ' .
                        'API endpoint - https://www.shippypro.com/api/  ' .
                        'Request - ' . json_encode($postParam) . '  ' .
                        $response2
                );
                $order->save();

                // End of pdf code


                // Send Invoice
                // generate invoice pdf
                if ($order->canInvoice()) {
                    $invoice = $invoiceService->prepareInvoice($order);
                    $invoice->register();
                    $invoice->save();
                }

                $invoiceId = '';
                if (count($order->getInvoiceCollection())) {
                    foreach ($order->getInvoiceCollection() as $invoice) {
                        $invoiceId = $invoice->getData('entity_id');
                    }
                }

                $pdfContent = '';
                $fileName2 = 'invoice' . $order->getIncrementId() . '.pdf';
                if ($invoiceId != '') {
                    $invoice = $objectManager->create(
                        \Magento\Sales\Api\InvoiceRepositoryInterface::class
                    )->get($invoiceId);

                    $pdf = $objectManager->create(\Scrumwheel\Shippy\Model\Order\Pdf\Invoice::class)->getPdf([$invoice]);
                    $pdfContent = ['type' => 'string', 'value' => $pdf->render()];

                    $fileFactory = $objectManager->create('Magento\Framework\App\Response\Http\FileFactory');
                    $fileSystem = $objectManager->create('Magento\Framework\Filesystem');
                    $fileFactory->create(
                        $fileName2,
                        $pdfContent,
                        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                        'application/pdf'
                    );
                }

                $fileName2 = 'invoice' . $order->getIncrementId() . '.pdf';
                $filePath2 = $varDirectory->getAbsolutePath($fileName2);
                $pdfContent2 = '';
                if ($varDirectory->isExist($fileName2)) {
                    $pdfContent2 = base64_encode(file_get_contents($filePath2));
                }

                $postParam2 = [
                    "Method" => "UploadPaperlessDocumentation",
                    "Params" => [
                        "TransactionID" => $order->getIncrementId(),
                        "Name" => $customerName,
                        "Country" => $shipping['country_id'],
                        "Document" => $pdfContent2,
                        "DocumentType" => 6
                    ]
                ];

                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://www.shippypro.com/api/',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($postParam2),
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Basic NTExN2Q5MzNhODlmNWQ1ZjY4YzRiNjgxZDA3YWM3MmE6',
                        'Content-Type: application/json',
                    ),
                ));

                $response3 = curl_exec($curl);

                curl_close($curl);

                $order2 = $objectManager->create('\Magento\Sales\Model\Order')->load($order->getId());
                $order2->addStatusHistoryComment(
                    'Shippypro api called  ' .
                        'API endpoint - https://www.shippypro.com/api/  ' .
                        'Request - ' . json_encode($postParam2) . '  ' .
                        $response3
                );
                $order2->save();
                // End of Invoice API

            }
        }

        // Your logic here to call the api
        $logger->info('Delayed shippy api cron job executed.');
    }
}
