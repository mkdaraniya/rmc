<?php

namespace BexioSync\BexioSync\Cron;

class PriceStockCron
{
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $apiUri = 'https://e1a5d7bf-2a7e-49ea-ad93-2a8223a900ca.mock.pstmn.io/';

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $storeRepositroy = $objectManager->get('\Magento\Store\Api\StoreRepositoryInterface');
        $stores = $storeRepositroy->getList();

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
        $logger->info("Product Sync Cron Job Start");

        try {

            $proResponse = [
                [
                    "id" => 1,
                    "title" => "iPhone 9",
                    "description" => "An apple mobile which is nothing like apple",
                    "price" => 1000,
                    "discountPercentage" => 12.96,
                    "rating" => 4.69,
                    "stock" => 10,
                    "brand" => "Apple",
                    "category" => "smartphones",
                ],
                [
                    "id" => 2,
                    "title" => "iPhone X",
                    "description" =>
                    "SIM-Free, Model A19211 6.5-inch Super Retina HD display with OLED technology A12 Bionic chip with ...",
                    "price" => 1100,
                    "discountPercentage" => 17.94,
                    "rating" => 4.44,
                    "stock" => 20,
                    "brand" => "Apple",
                    "category" => "smartphones",
                ],
                [
                    "id" => 3,
                    "title" => "Samsung Universe 9",
                    "description" =>
                    "Samsung\'s new variant which goes beyond Galaxy to the Universe",
                    "price" => 1200,
                    "discountPercentage" => 15.46,
                    "rating" => 4.09,
                    "stock" => 30,
                    "brand" => "Samsung",
                    "category" => "smartphones",
                ],
                [
                    "id" => 4,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 5,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 6,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 7,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 8,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 9,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 10,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 1,
                    "title" => "iPhone 9",
                    "description" => "An apple mobile which is nothing like apple",
                    "price" => 1000,
                    "discountPercentage" => 12.96,
                    "rating" => 4.69,
                    "stock" => 10,
                    "brand" => "Apple",
                    "category" => "smartphones",
                ],
                [
                    "id" => 2,
                    "title" => "iPhone X",
                    "description" =>
                    "SIM-Free, Model A19211 6.5-inch Super Retina HD display with OLED technology A12 Bionic chip with ...",
                    "price" => 1100,
                    "discountPercentage" => 17.94,
                    "rating" => 4.44,
                    "stock" => 20,
                    "brand" => "Apple",
                    "category" => "smartphones",
                ],
                [
                    "id" => 3,
                    "title" => "Samsung Universe 9",
                    "description" =>
                    "Samsung\'s new variant which goes beyond Galaxy to the Universe",
                    "price" => 1200,
                    "discountPercentage" => 15.46,
                    "rating" => 4.09,
                    "stock" => 30,
                    "brand" => "Samsung",
                    "category" => "smartphones",
                ],
                [
                    "id" => 4,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 5,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 6,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 7,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 8,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 9,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
                [
                    "id" => 10,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 1300,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 40,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                ],
            ];

            foreach ($proResponse as $key => $pro) {

                $sku = $pro['title'];

                foreach($stores as $store){
                    $storeId = $store->getId();
                    
                    $product = $objectManager->get('Magento\Catalog\Model\Product');
                    if($product->getIdBySku($sku)) {
                        // $product2 = $product->getIdBySku($sku);
                        $product->setPrice($pro['price']); // price of product
                        $product->setStoreId($storeId); // set specific store for product price
                        $product->setStockData(
                            array(
                                'qty' => $pro['stock']
                            )
                        );
                        $product->save();

                        $logger->info("Product Sku : " . $product->getSku());

                    }else{
                        $logger->info("Product Sku not exist : " . $sku);
                    }
                }
            }

            $logger->info("Product Sync Cron Job End");

            return $this;
        } catch (\Exception $e) {
            return $logger->critical($e);
        }
        return true;
    }
}
