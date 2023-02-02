<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\MultiStoreViewPricing\Model\ResourceModel\Product;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Option extends \Magento\Catalog\Model\ResourceModel\Product\Option
{
    /**
     * Save value prices
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _saveValuePrices(AbstractModel $object)
    {
        if (in_array($object->getType(), $this->getPriceTypes())) {
            
            $scope = (int)$this->_config->getValue(
                Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($scope == 2) {
                
                $connection = $this->getConnection();
                $priceTableName = $this->getTable('catalog_product_option_price');
                $isDeleteStorePrice = (bool)$object->getData('is_delete_store_price');
                foreach ([Store::DEFAULT_STORE_ID, $object->getStoreId()] as $storeId) {
                    $existInCurrentStore = $this->getColFromOptionTable($priceTableName, (int)$object->getId(), (int)$storeId);
                    $existInDefaultStore = (int)$storeId == Store::DEFAULT_STORE_ID ?
                        $existInCurrentStore :
                        $this->getColFromOptionTable(
                            $priceTableName,
                            (int)$object->getId(),
                            Store::DEFAULT_STORE_ID
                        );

                    $isDeleteStoreTitle = (bool)$object->getData('is_delete_store_price');
                    if ($existInCurrentStore) {
                        if ($isDeleteStoreTitle && (int)$storeId != Store::DEFAULT_STORE_ID) {
                            $connection->delete($priceTableName, ['option_price_id = ?' => $existInCurrentStore]);
                        } elseif ($object->getStoreId() == $storeId) {
                            $newPrice = $this->calculateStorePrice($object, $storeId);
                            $this->savePriceByStore($object, (int)$storeId, $newPrice);
                        }
                    } else {
                        // we should insert record into not default store only of if it does not exist in default store
                        if (($storeId == Store::DEFAULT_STORE_ID && !$existInDefaultStore) ||
                            (
                                $storeId != Store::DEFAULT_STORE_ID &&
                                !$existInCurrentStore &&
                                !$isDeleteStoreTitle
                            )
                        ) {
                            $newPrice = $this->calculateStorePrice($object, $storeId);
                            $this->savePriceByStore($object, (int)$storeId, $newPrice);
                        }
                    }
                    
                }
            } else {
               parent::_saveValuePrices($object);
            }
        }

        return $this;
    }

    /**
     * Save option price by store
     *
     * @param AbstractModel $object
     * @param int $storeId
     * @param float|null $newPrice
     */
    private function savePriceByStore(AbstractModel $object, int $storeId, float $newPrice = null): void
    {
        $priceTable = $this->getTable('catalog_product_option_price');
        $connection = $this->getConnection();
        $price = $newPrice === null ? $object->getPrice() : $newPrice;

        $statement = $connection->select()->from($priceTable, 'option_id')
            ->where('option_id = ?', $object->getId())
            ->where('store_id = ?', $storeId);
        $optionId = $connection->fetchOne($statement);

        if (!$optionId) {
            $data = $this->_prepareDataForTable(
                new DataObject(
                    [
                        'option_id' => $object->getId(),
                        'store_id' => $storeId,
                        'price' => $price,
                        'price_type' => $object->getPriceType(),
                    ]
                ),
                $priceTable
            );
            $connection->insert($priceTable, $data);
        } else {
            // skip to update the default price when the store price is saving
            if ($storeId === Store::DEFAULT_STORE_ID && (int)$object->getStoreId() !== $storeId) {
                return;
            }

            $data = $this->_prepareDataForTable(
                new DataObject(
                    [
                        'price' => $price,
                        'price_type' => $object->getPriceType()
                    ]
                ),
                $priceTable
            );

            $connection->update(
                $priceTable,
                $data,
                [
                    'option_id = ?' => $object->getId(),
                    'store_id  = ?' => $storeId
                ]
            );
        }
    }

    /**
     * Calculate price by store
     *
     * @param AbstractModel $object
     * @param int $storeId
     * @return float
     */
    private function calculateStorePrice(AbstractModel $object, int $storeId): float
    {
        $price = $object->getPrice();
        return (float)$price;
    }

    /**
     * Get Metadata Pool
     *
     * @return \Magento\Framework\EntityManager\MetadataPool
     */
    private function getMetadataPool()
    {
        if (null === $this->metadataPool) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\EntityManager\MetadataPool::class);
        }
        return $this->metadataPool;
    }
}
