<?php

namespace BexioSync\BexioSync\Cron;

class ProductCron
{

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager
        //        $apiUri = 'https://bexio.free.beeceptor.com/2.0/article';
        $apiUri = 'https://api.bexio.com/2.0/article';
        $scopeConfig = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');

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
        $logger->info("Product Sync Cron Job Start");

        try {

            /* create custom attribute if not exist */
            $attributeArr = [
                'external_product_id' => 'External Product ID',
                'external_product_source' => 'External Product Source'
            ];
            foreach ($attributeArr as $attrCode => $attrName) {
                $attribute = $eavConfig->getAttribute('catalog_product', $attrCode);
                // Check Attribute already exist or not
                if (!$attribute || !$attribute->getAttributeId()) {
                    $objectManager->create('\BexioSync\BexioSync\Helper\Attribute')->createProductAttribute($attrCode, $attrName);
                }
            }
            /** End of attribute creating */

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $apiUri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Authorization: Bearer eyJraWQiOiI2ZGM2YmJlOC1iMjZjLTExZTgtOGUwZC0wMjQyYWMxMTAwMDIiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJtYXJqYW5AYnVjaGVtLmNvbSIsImxvZ2luX2lkIjoiNmIyY2ZlM2UtNTVhYS00OTM0LThjY2ItNzgxMGU4ZjY3MTc4IiwiY29tcGFueV9pZCI6ImtmeWVqYnJxM3BsdiIsInVzZXJfaWQiOjMzNTkzNiwiYXpwIjoiZXZlcmxhc3QtdG9rZW4tb2ZmaWNlLWNsaWVudCIsInNjb3BlIjoib3BlbmlkIHByb2ZpbGUgZW1haWwgYWxsIHRlY2huaWNhbCIsImlzcyI6Imh0dHBzOlwvXC9pZHAuYmV4aW8uY29tIiwiZXhwIjozMjU3MzI1NDgwLCJpYXQiOjE2ODA1MjU0ODAsImNvbXBhbnlfdXNlcl9pZCI6MSwianRpIjoiZTE3ZWQ4YTMtZmRkYy00M2VkLTllMjItNjdiMDhjMWJjNjNjIn0.L4rNyP-ypCp890rWWc2cSCxcelf1FuY_rpvNI6YgiC0n69fgt_ilpEZmnZXuBSoVxzusWA9FuCk9MOWqjatFurU9BQy5kwt-sADDAgVWfoGFHiyZR1SFbq5P26zgofpwAA1zoVk7YudEEkl5SONTYpXXgQApmiSh5B7matjPGfBlk8qLhHxLFQYM3YPJaS-7Yp8kIvuOn6sSFs3WvuL-Wqfb96qQKxkB0oUEXrVy1aPxV7Xt4TC5edPGbD0CLX7SbGUZiNFWm59IQS9zeRIJPr5HufokhaeRXfull4xUy4uO-VxT535kAUxH_gBpjW8jhgC4TldkKMcIFsqoWKuI3sxL_8iLXCCF1dDytUUwrUoVvQs1NFV0WRpuE0R_IUQL6vlsFRbLvy-NLe0rV0r8Nei06cq-zcwNaGmnfUvUjSMTSwVpKtftxYXwhY8WipB4Jo4D_LSfUDx0kpOEAxFwwOB4J3EsO9TheQ9i8b3q2jhykrDUfKsiechH7uNiyvN-c2PL8NQd-rpDDZoJP9A3_ywZ2sOThDIEese6lrJtNibBY4i2ZIb7_FX5lg1du9KP3vgPLRgyYkoLe-zF5TLkZao75Y8KBU1YgteLRbE-VC4kUnTBrfmDukxqx8kvPP0ljp2on7HdpA7F1wj1sj_hvKlCcqxxZjwiBqsjjy41FUA'
                ),
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $apiResult = json_decode($response);

            foreach ($apiResult as $key => $pro) {

                $sku = $pro->intern_code;

                $product = $objectManager->get('Magento\Catalog\Model\Product');

                if (!$product->getIdBySku($sku)) {

                    $product = $objectManager->create('\Magento\Catalog\Model\Product');
                    $product->setSku($pro->intern_code); // Set your sku here
                    $product->setName($pro->intern_name); // Name of Product
                    $product->setDescription($pro->intern_description); // Description of Product
                    $product->setAttributeSetId(4)->setWebsiteIds(array(1)) # Website id, 1 is default            
                        ->setStoreId(0); // Attribute set id
                    $product->setStatus(1); // Status on product enabled/ disabled 1/0
                    if ($pro->weight != NULL || $pro->weight != '') {
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
                } else {
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
