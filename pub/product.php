<?php

echo "script start";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('memory_limit', '5G');
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require '../app/bootstrap.php';

$bootstrap = Bootstrap::create(BP, $_SERVER);

global $objectManager;
$objectManager = $bootstrap->getObjectManager();

$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

$storeRepositroy = $objectManager->get('\Magento\Store\Api\StoreRepositoryInterface');
$stores = $storeRepositroy->getList();

$writer = new \Zend_Log_Writer_Stream(BP . '/var/log/scrumwheel_bexiosync.log');
$logger = new \Zend_Log();
$logger->addWriter($writer);
$logger->info(__METHOD__);


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
echo $response;





/**
 * get single product using api
 */
// $curl = curl_init();

// curl_setopt_array($curl, array(
//     CURLOPT_URL => 'https://bexio.free.beeceptor.com/2.0/article',
//     CURLOPT_RETURNTRANSFER => true,
//     CURLOPT_ENCODING => '',
//     CURLOPT_MAXREDIRS => 10,
//     CURLOPT_TIMEOUT => 0,
//     CURLOPT_FOLLOWLOCATION => true,
//     CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//     CURLOPT_CUSTOMREQUEST => 'GET',
// ));

// $response = curl_exec($curl);

// curl_close($curl);
// echo $response;

die;

$proResponse = [
    [
        "id" => 1,
        "title" => "iPhone 9",
        "description" => "An apple mobile which is nothing like apple",
        "price" => 549,
        "discountPercentage" => 12.96,
        "rating" => 4.69,
        "stock" => 11,
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
        "price" => 12,
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
        "price" => 13,
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
        "price" => 14,
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
    ]
];


function _getResourceConnection()
{
    global $objectManager;
    return $objectManager->get('Magento\Framework\App\ResourceConnection');
}

function _getReadConnection()
{
    return _getConnection('core_read');
}

function _getWriteConnection()
{
    return _getConnection('core_write');
}

function _getConnection($type = 'core_read')
{
    return _getResourceConnection()->getConnection($type);
}

function _getTableName($tableName)
{
    return _getResourceConnection()->getTableName($tableName);
}

function _getAttributeId($attributeCode)
{
    $connection = _getReadConnection();
    $sql = "SELECT attribute_id FROM " . _getTableName('eav_attribute') . " WHERE entity_type_id = ? AND attribute_code = ?";
    return $connection->fetchOne(
        $sql,
        [
            _getEntityTypeId('catalog_product'),
            $attributeCode
        ]
    );
}

function _getEntityTypeId($entityTypeCode)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_type_id FROM " . _getTableName('eav_entity_type') . " WHERE entity_type_code = ?";
    return $connection->fetchOne(
        $sql,
        [
            $entityTypeCode
        ]
    );
}

function _getIdFromSku($sku)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT entity_id FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne(
        $sql,
        [
            $sku
        ]
    );
}

function checkIfSkuExists($sku)
{
    $connection = _getConnection('core_read');
    $sql        = "SELECT COUNT(*) AS count_no FROM " . _getTableName('catalog_product_entity') . " WHERE sku = ?";
    return $connection->fetchOne($sql, [$sku]);
}

// create price array for sql query
$connection     = _getWriteConnection();
$attributeId    = _getAttributeId('price');
$priceArr = [];
foreach ($stores as $store) {
    $storeId = $store->getId();

    foreach ($proResponse as $key => $pro) {
        $entityId = _getIdFromSku($pro['title']);
        $currentPrice = $pro['price'];

        $priceArr[] = '(' . $attributeId . ',' . $storeId . ',' . $entityId . ',' . $currentPrice . ')';
        $implodePrice = '(' . $attributeId . ',' . $storeId . ',' . $entityId . ',' . $currentPrice . ')';
        $output = $connection->query("INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) VALUES " . $implodePrice . " ON DUPLICATE KEY UPDATE value=VALUES(value)");
    }
}

// INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) 
//     VALUES (77,1,4942,1000),(77,1,4943,2000),(77,1,4944,3000),(77,1,4945,4000)
//     ON DUPLICATE KEY UPDATE value=VALUES(value);

try {
    $implodePrice = implode(",", $priceArr);

    echo $implodePrice;

    $connection     = _getWriteConnection();
    $output = $connection->query("INSERT INTO catalog_product_entity_decimal (attribute_id, store_id, entity_id, value) VALUES " . $implodePrice . " ON DUPLICATE KEY UPDATE value=VALUES(value)");
    print_r($output);
} catch (\Exception $e) {
    echo $e->getMessage();
}
