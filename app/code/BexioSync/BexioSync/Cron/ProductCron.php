<?php

namespace BexioSync\BexioSync\Cron;

class ProductCron
{

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        $apiUri = 'https://bexio.free.beeceptor.com/2.0/article';

        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

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

            /* create custom attribute if not exist */
            $attributeArr = [
                'external_product_id' => 'External Product ID',
                'external_product_source' => 'External Product Source'
            ];
            foreach($attributeArr as $attrCode => $attrName){
                $attribute = $eavConfig->getAttribute('catalog_product', $attrCode);
                // Check Attribute already exist or not
                if (!$attribute || !$attribute->getAttributeId()) {
                    $objectManager->create('\BexioSync\BexioSync\Helper\Attribute')->createProductAttribute($attrCode,$attrName);
                }
            }
            /** End of attribute creating */

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $apiResult = json_decode($response);


            $proResponse = [
                [
                    "id" => 1,
                    "title" => "iPhone 9",
                    "description" => "An apple mobile which is nothing like apple",
                    "price" => 549,
                    "discountPercentage" => 12.96,
                    "rating" => 4.69,
                    "stock" => 94,
                    "brand" => "Apple",
                    "category" => "smartphones",
                    "thumbnail" =>
                    "https://i.dummyjson.com/data/products/1/thumbnail.jpg",
                    "images" => [
                        "https://i.dummyjson.com/data/products/1/1.jpg",
                        "https://i.dummyjson.com/data/products/1/2.jpg",
                        "https://i.dummyjson.com/data/products/1/3.jpg",
                        "https://i.dummyjson.com/data/products/1/4.jpg",
                        "https://i.dummyjson.com/data/products/1/thumbnail.jpg",
                    ],
                ],
                [
                    "id" => 2,
                    "title" => "iPhone X",
                    "description" =>
                    "SIM-Free, Model A19211 6.5-inch Super Retina HD display with OLED technology A12 Bionic chip with ...",
                    "price" => 899,
                    "discountPercentage" => 17.94,
                    "rating" => 4.44,
                    "stock" => 34,
                    "brand" => "Apple",
                    "category" => "smartphones",
                    "thumbnail" =>
                    "https://i.dummyjson.com/data/products/2/thumbnail.jpg",
                    "images" => [
                        "https://i.dummyjson.com/data/products/2/1.jpg",
                        "https://i.dummyjson.com/data/products/2/2.jpg",
                        "https://i.dummyjson.com/data/products/2/3.jpg",
                        "https://i.dummyjson.com/data/products/2/thumbnail.jpg",
                    ],
                ],
                [
                    "id" => 3,
                    "title" => "Samsung Universe 9",
                    "description" =>
                    "Samsung\'s new variant which goes beyond Galaxy to the Universe",
                    "price" => 1249,
                    "discountPercentage" => 15.46,
                    "rating" => 4.09,
                    "stock" => 36,
                    "brand" => "Samsung",
                    "category" => "smartphones",
                    "thumbnail" =>
                    "https://i.dummyjson.com/data/products/3/thumbnail.jpg",
                    "images" => ["https://i.dummyjson.com/data/products/3/1.jpg"],
                ],
                [
                    "id" => 4,
                    "title" => "OPPOF19",
                    "description" => "OPPO F19 is officially announced on April 2021.",
                    "price" => 280,
                    "discountPercentage" => 17.91,
                    "rating" => 4.3,
                    "stock" => 123,
                    "brand" => "OPPO",
                    "category" => "smartphones",
                    "thumbnail" =>
                    "https://i.dummyjson.com/data/products/4/thumbnail.jpg",
                    "images" => [
                        "https://i.dummyjson.com/data/products/4/1.jpg",
                        "https://i.dummyjson.com/data/products/4/2.jpg",
                        "https://i.dummyjson.com/data/products/4/3.jpg",
                        "https://i.dummyjson.com/data/products/4/4.jpg",
                        "https://i.dummyjson.com/data/products/4/thumbnail.jpg",
                    ],
                ],
            ];

            foreach ($apiResult as $key => $pro) {

                $sku = $pro->intern_code;

                $product = $objectManager->get('Magento\Catalog\Model\Product');

                if(!$product->getIdBySku($sku)) {

                    $product = $objectManager->create('\Magento\Catalog\Model\Product');
                    $product->setSku($pro->intern_code); // Set your sku here
                    $product->setName($pro->intern_name); // Name of Product
                    $product->setAttributeSetId(4); // Attribute set id
                    $product->setStatus(1); // Status on product enabled/ disabled 1/0
                    if($pro->weight != NULL || $pro->weight != ''){
                        $product->setWeight($pro->weight); // weight of product
                    }
                    $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                    $product->setTaxClassId(0); // Tax class id
                    $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                    $product->setPrice($pro->sale_price); // price of product
                    $product->setStockData(
                        array(
                            'use_config_manage_stock' => 0,
                            'manage_stock' => 1,
                            'is_in_stock' => $pro->is_stock,
                            'qty' => $pro->stock_available_nr
                        )
                    );
                    $product->setData('external_product_id', $pro->id);
                    $product->setData('external_product_source', 'Bexio API');
                    $product->save();

                    $logger->info("Product Id : " . $product->getId());

                }else{
                    $logger->info("Product Already Exist Sku : " . $sku);
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
