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
 * @package    Bss_MultiStoreViewPricingPriceIndexer
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\ResourceModel\Product\Indexer\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ObjectManager;

class Bundle extends \Bss\MultiStoreViewPricingPriceIndexer\Model\ResourceModel\Product\Indexer\Base\Bundle
{
    /**
     * Reindex temporary (price result data) for all products
     *
     * @return $this
     * @throws \Exception
     */
    public function reindexAll()
    {
        $this->tableStrategy->setUseIdxTable(true);

        $this->beginTransaction();
        try {
            $this->_prepareBundlePrice();
            $this->commit();
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Reindex temporary (price result data) for defined product(s)
     *
     * @param  int|array $entityIds
     * @return $this
     */
    public function reindexEntity($entityIds)
    {
        $this->_prepareBundlePrice($entityIds);

        return $this;
    }

    /**
     * Retrieve temporary price index table name for fixed bundle products
     *
     * @return string
     */
    protected function _getBundlePriceTable()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_getBundlePriceTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle_store');
    }

    /**
     * Retrieve table name for temporary bundle selection prices index
     *
     * @return string
     */
    protected function _getBundleSelectionTable()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_getBundleSelectionTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle_sel_store');
    }

    /**
     * Retrieve table name for temporary bundle option prices index
     *
     * @return string
     */
    protected function _getBundleOptionTable()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_getBundleOptionTable();
        }

        return $this->tableStrategy->getTableName('catalog_product_index_price_bundle_opt_store');
    }

    /**
     * Prepare temporary price index table for fixed bundle products
     *
     * @return $this
     */
    protected function _prepareBundlePriceTable()
    {
        $this->getConnection()->delete($this->_getBundlePriceTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle selection prices index
     *
     * @return $this
     */
    protected function _prepareBundleSelectionTable()
    {
        $this->getConnection()->delete($this->_getBundleSelectionTable());
        return $this;
    }

    /**
     * Prepare table structure for temporary bundle option prices index
     *
     * @return $this
     */
    protected function _prepareBundleOptionTable()
    {
        $this->getConnection()->delete($this->_getBundleOptionTable());
        return $this;
    }

    /**
     * Prepare temporary price index data for bundle products by price type
     *
     * @param                                         int       $priceType
     * @param                                         int|array $entityIds the entity ids limitation
     * @return                                        $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareBundlePriceByType($priceType, $entityIds = null)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_prepareBundlePriceByType($priceType, $entityIds);
        }

        $connection = $this->getConnection();
        $table = $this->_getBundlePriceTable();

        $select = $connection->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['entity_id']
        )->join(
            ['cg' => $this->getTable('customer_group')],
            '',
            ['customer_group_id']
        );
        $this->_addWebsiteJoinToSelect($select, true);
        $this->_addProductWebsiteJoinToSelect($select, 'cw.website_id', "e.entity_id");
        $select->columns(
            'website_id',
            'cw'
        )->columns(
            'store_id',
            'cs'
        )->join(
            ['cwd' => $this->_getWebsiteDateTable()],
            'cw.website_id = cwd.website_id',
            []
        )->joinLeft(
            ['tp' => $this->_getTierPriceIndexTable()],
            'tp.entity_id = e.entity_id AND tp.store_id = cs.store_id' .
            ' AND tp.customer_group_id = cg.customer_group_id',
            []
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );

        // add enable products limitation
        $statusCond = $connection->quoteInto(
            '=?',
            \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        );
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $this->_addAttributeToSelect($select, 'status', "e.$linkField", 'cs.store_id', $statusCond, true);
        if ($this->moduleManager->isEnabled('Magento_Tax')) {
            $taxClassId = $this->_addAttributeToSelect($select, 'tax_class_id', "e.$linkField", 'cs.store_id');
        } else {
            $taxClassId = new \Zend_Db_Expr('0');
        }

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC) {
            $select->columns(['tax_class_id' => new \Zend_Db_Expr('0')]);
        } else {
            $select->columns(
                ['tax_class_id' => $connection->getCheckSql($taxClassId . ' IS NOT NULL', $taxClassId, 0)]
            );
        }

        $priceTypeCond = $connection->quoteInto('=?', $priceType);
        $this->_addAttributeToSelect($select, 'price_type', "e.$linkField", 'cs.store_id', $priceTypeCond);

        $price = $this->_addAttributeToSelect($select, 'price', "e.$linkField", 'cs.store_id');
        $specialPrice = $this->_addAttributeToSelect($select, 'special_price', "e.$linkField", 'cs.store_id');
        $specialFrom = $this->_addAttributeToSelect($select, 'special_from_date', "e.$linkField", 'cs.store_id');
        $specialTo = $this->_addAttributeToSelect($select, 'special_to_date', "e.$linkField", 'cs.store_id');
        $curentDate = new \Zend_Db_Expr('cwd.website_date');

        $specialExpr = $connection->getCheckSql(
            $connection->getCheckSql(
                $specialFrom . ' IS NULL',
                '1',
                $connection->getCheckSql($specialFrom . ' <= ' . $curentDate, '1', '0')
            ) . " > 0 AND " . $connection->getCheckSql(
                $specialTo . ' IS NULL',
                '1',
                $connection->getCheckSql($specialTo . ' >= ' . $curentDate, '1', '0')
            ) . " > 0 AND {$specialPrice} > 0 AND {$specialPrice} < 100 ",
            $specialPrice,
            '0'
        );

        $tierExpr = new \Zend_Db_Expr("tp.min_price");

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $finalPrice = $connection->getCheckSql(
                $specialExpr . ' > 0',
                'ROUND(' . $price . ' * (' . $specialExpr . '  / 100), 4)',
                $price
            );
            $tierPrice = $connection->getCheckSql(
                $tierExpr . ' IS NOT NULL',
                'ROUND(' . $price . ' - ' . '(' . $price . ' * (' . $tierExpr . ' / 100)), 4)',
                'NULL'
            );

            $finalPrice = $connection->getCheckSql(
                "{$tierPrice} < {$finalPrice}",
                $tierPrice,
                $finalPrice
            );
        } else {
            $finalPrice = new \Zend_Db_Expr("0");
            $tierPrice = $connection->getCheckSql($tierExpr . ' IS NOT NULL', '0', 'NULL');
        }

        $select->columns(
            [
                'price_type' => new \Zend_Db_Expr($priceType),
                'special_price' => $specialExpr,
                'tier_percent' => $tierExpr,
                'orig_price' => $connection->getCheckSql($price . ' IS NULL', '0', $price),
                'price' => $finalPrice,
                'min_price' => $finalPrice,
                'max_price' => $finalPrice,
                'tier_price' => $tierPrice,
                'base_tier' => $tierPrice,
            ]
        );

        if ($entityIds !== null) {
            $select->where('e.entity_id IN(?)', $entityIds);
        }

        /**
         * Add additional external limitation
         */
        $this->_eventManager->dispatch(
            'catalog_product_prepare_index_select',
            [
                'select' => $select,
                'entity_field' => new \Zend_Db_Expr('e.entity_id'),
                'website_field' => new \Zend_Db_Expr('cw.website_id'),
                'store_field' => new \Zend_Db_Expr('cs.store_id')
            ]
        );

        $query = $select->insertFromSelect($table);
        $connection->query($query);

        return $this;
    }

    /**
     * Calculate fixed bundle product selections price
     *
     * @return $this
     */
    protected function _calculateBundleOptionPrice()
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_calculateBundleOptionPrice();
        }

        $connection = $this->getConnection();

        $this->_prepareBundleSelectionTable();
        $this->_calculateBundleSelectionPrice(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED);
        $this->_calculateBundleSelectionPrice(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC);

        $this->_prepareBundleOptionTable();

        $select = $connection->select()->from(
            ['i' => $this->_getBundleSelectionTable()],
            ['entity_id', 'customer_group_id', 'website_id', 'store_id', 'option_id']
        )->group(
            ['entity_id', 'customer_group_id', 'website_id', 'store_id', 'option_id', 'is_required', 'group_type']
        )->columns(
            [
                'min_price' => $connection->getCheckSql('i.is_required = 1', 'MIN(i.price)', '0'),
                'alt_price' => $connection->getCheckSql('i.is_required = 0', 'MIN(i.price)', '0'),
                'max_price' => $connection->getCheckSql('i.group_type = 1', 'SUM(i.price)', 'MAX(i.price)'),
                'tier_price' => $connection->getCheckSql('i.is_required = 1', 'MIN(i.tier_price)', '0'),
                'alt_tier_price' => $connection->getCheckSql('i.is_required = 0', 'MIN(i.tier_price)', '0'),
            ]
        );

        $query = $select->insertFromSelect($this->_getBundleOptionTable());
        $connection->query($query);

        $this->_prepareDefaultFinalPriceTable();

        $minPrice = new \Zend_Db_Expr(
            $connection->getCheckSql('SUM(io.min_price) = 0', 'MIN(io.alt_price)', 'SUM(io.min_price)') . ' + i.price'
        );
        $maxPrice = new \Zend_Db_Expr("SUM(io.max_price) + i.price");
        $tierPrice = $connection->getCheckSql(
            'MIN(i.tier_percent) IS NOT NULL',
            $connection->getCheckSql(
                'SUM(io.tier_price) = 0',
                'SUM(io.alt_tier_price)',
                'SUM(io.tier_price)'
            ) . ' + MIN(i.tier_price)',
            'NULL'
        );

        $select = $connection->select()->from(
            ['io' => $this->_getBundleOptionTable()],
            ['entity_id', 'customer_group_id', 'website_id', 'store_id']
        )->join(
            ['i' => $this->_getBundlePriceTable()],
            'i.entity_id = io.entity_id AND i.customer_group_id = io.customer_group_id' .
            ' AND i.website_id = io.website_id AND i.store_id = io.store_id',
            []
        )->group(
            ['io.entity_id', 'io.customer_group_id', 'io.website_id', 'io.store_id', 'i.tax_class_id', 'i.orig_price', 'i.price']
        )->columns(
            [
                'i.tax_class_id',
                'orig_price' => 'i.orig_price',
                'price' => 'i.price',
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'tier_price' => $tierPrice,
                'base_tier' => 'MIN(i.base_tier)',
            ]
        );

        $query = $select->insertFromSelect($this->_getDefaultFinalPriceTable());
        $connection->query($query);

        return $this;
    }

    /**
     * Calculate bundle product selections price by product type
     *
     * @param                                         int $priceType
     * @return                                        $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _calculateBundleSelectionPrice($priceType)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_calculateBundleSelectionPrice($priceType);
        }

        $connection = $this->getConnection();

        if ($priceType == \Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED) {
            $selectionPriceValue = $connection->getCheckSql(
                'bsp.selection_price_value IS NULL',
                'bs.selection_price_value',
                'bsp.selection_price_value'
            );
            $selectionPriceType = $connection->getCheckSql(
                'bsp.selection_price_type IS NULL',
                'bs.selection_price_type',
                'bsp.selection_price_type'
            );
            $priceExpr = new \Zend_Db_Expr(
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.price * (' . $selectionPriceValue . ' / 100),4)',
                    $connection->getCheckSql(
                        'i.special_price > 0 AND i.special_price < 100',
                        'ROUND(' . $selectionPriceValue . ' * (i.special_price / 100),4)',
                        $selectionPriceValue
                    )
                ) . '* bs.selection_qty'
            );

            $tierExpr = $connection->getCheckSql(
                'i.base_tier IS NOT NULL',
                $connection->getCheckSql(
                    $selectionPriceType . ' = 1',
                    'ROUND(i.base_tier - (i.base_tier * (' . $selectionPriceValue . ' / 100)),4)',
                    $connection->getCheckSql(
                        'i.tier_percent > 0',
                        'ROUND(' .
                        $selectionPriceValue .
                        ' - (' .
                        $selectionPriceValue .
                        ' * (i.tier_percent / 100)),4)',
                        $selectionPriceValue
                    )
                ) . ' * bs.selection_qty',
                'NULL'
            );

            $priceExpr = new \Zend_Db_Expr(
                $connection->getCheckSql("{$tierExpr} < {$priceExpr}", $tierExpr, $priceExpr)
            );
        } else {
            $priceExpr = new \Zend_Db_Expr(
                $connection->getCheckSql(
                    'i.special_price > 0 AND i.special_price < 100',
                    'ROUND(idx.min_price * (i.special_price / 100), 4)',
                    'idx.min_price'
                ) . ' * bs.selection_qty'
            );
            $tierExpr = $connection->getCheckSql(
                'i.base_tier IS NOT NULL',
                'ROUND(idx.min_price * (i.base_tier / 100), 4)* bs.selection_qty',
                'NULL'
            );
        }

        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        $select = $connection->select()->from(
            ['i' => $this->_getBundlePriceTable()],
            ['entity_id', 'customer_group_id', 'website_id', 'store_id']
        )->join(
            ['parent_product' => $this->getTable('catalog_product_entity')],
            'parent_product.entity_id = i.entity_id',
            []
        )->join(
            ['bo' => $this->getTable('catalog_product_bundle_option')],
            "bo.parent_id = parent_product.$linkField",
            ['option_id']
        )->join(
            ['bs' => $this->getTable('catalog_product_bundle_selection')],
            'bs.option_id = bo.option_id',
            ['selection_id']
        )->joinLeft(
            ['bsp' => $this->getTable('catalog_product_bundle_selection_price')],
            'bs.selection_id = bsp.selection_id AND bsp.website_id = i.website_id',
            ['']
        )->join(
            ['idx' => $this->_getDefaultFinalPriceTable()],
            'bs.product_id = idx.entity_id AND i.customer_group_id = idx.customer_group_id' .
            ' AND i.website_id = idx.website_id and i.store_id = idx.store_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'bs.product_id = e.entity_id AND e.required_options=0',
            []
        )->where(
            'i.price_type=?',
            $priceType
        )->columns(
            [
                'group_type' => $connection->getCheckSql("bo.type = 'select' OR bo.type = 'radio'", '0', '1'),
                'is_required' => 'bo.required',
                'price' => $priceExpr,
                'tier_price' => $tierExpr,
            ]
        );

        $query = $select->insertFromSelect($this->_getBundleSelectionTable());
        $connection->query($query);

        return $this;
    }

    /**
     * Prepare temporary index price for bundle products
     *
     * @param  int|array $entityIds the entity ids limitation
     * @return $this
     */
    protected function _prepareBundlePrice($entityIds = null)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_prepareBundlePrice($entityIds);
        }

        if (!$this->hasEntity() && empty($entityIds)) {
            return $this;
        }

        if (!empty($entityIds)) {
            $allEntityIds = $this->getRelatedProducts($entityIds);
            $this->prepareFinalPriceDataForType($allEntityIds, null);
        } else {
            $this->_prepareFinalPriceData($entityIds);
        }

        $this->_prepareTierPriceIndex($entityIds);
        $this->_prepareBundlePriceTable();
        $this->_prepareBundlePriceByType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_FIXED, $entityIds);
        $this->_prepareBundlePriceByType(\Magento\Bundle\Model\Product\Price::PRICE_TYPE_DYNAMIC, $entityIds);

        $this->_calculateBundleOptionPrice();
        $this->_applyCustomOption();

        $this->_movePriceDataToIndexTable();

        return $this;
    }

    private function getRelatedProducts($entityIds)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $select = $this->getConnection()->select()->union(
            [
                $this->getConnection()->select()
                    ->from(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'e.entity_id'
                    )->join(
                        ['cpbs' => $this->getTable('catalog_product_bundle_selection')],
                        'cpbs.parent_product_id = e.' . $metadata->getLinkField(),
                        []
                    )->join(
                        ['si' => $this->getTable('cataloginventory_stock_item')],
                        'si.product_id = cpbs.product_id',
                        []
                    )
                    ->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    )->where(
                        'si.is_in_stock = ?', \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK
                    ),
                $this->getConnection()->select()
                    ->from(
                        ['cpbs' => $this->getTable('catalog_product_bundle_selection')],
                        'cpbs.product_id'
                    )->join(
                        ['e' => $this->getTable('catalog_product_entity')],
                        'cpbs.parent_product_id = e.' . $metadata->getLinkField(),
                        []
                    )->join(
                        ['si' => $this->getTable('cataloginventory_stock_item')],
                        'si.product_id = cpbs.product_id',
                        []
                    )->where(
                        'si.is_in_stock = ?', \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK
                    )
                    ->where(
                        'e.entity_id IN (?)',
                        $entityIds
                    ),
                $this->getConnection()->select()
                    ->from(['cpbs' => $this->getTable('catalog_product_bundle_selection')], 'product_id')
                    ->join(
                        ['si' => $this->getTable('cataloginventory_stock_item')],
                        'si.product_id = cpbs.product_id',
                        []
                    )->where(
                        'si.is_in_stock = ?', \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK
                    )
                    ->where('cpbs.product_id in (?)', $entityIds),
            ]
        );

        return array_map('intval', $this->getConnection()->fetchCol($select));
    }

    /**
     * Prepare percentage tier price for bundle products
     *
     * @param  int|array $entityIds
     * @return $this
     */
    protected function _prepareTierPriceIndex($entityIds = null)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_prepareTierPriceIndex($entityIds);
        }

        $connection = $this->getConnection();
        $linkField = $this->getMetadataPool()->getMetadata(ProductInterface::class)->getLinkField();
        // remove index by bundle products
        $select = $connection->select()->from(
            ['i' => $this->_getTierPriceIndexTable()],
            null
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "i.entity_id=e.$linkField",
            []
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        );
        $query = $select->deleteFromSelect('i');
        $connection->query($query);

        $select = $connection->select()->from(
            ['tp' => $this->getTable('catalog_product_entity_tier_price_store')],
            [$linkField]
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            "tp.{$linkField} = e.{$linkField}",
            []
        )->join(
            ['cg' => $this->getTable('customer_group')],
            'tp.all_groups = 1 OR (tp.all_groups = 0 AND tp.customer_group_id = cg.customer_group_id)',
            ['customer_group_id']
        )->join(
            ['cs' => $this->getTable('store')],
            'tp.store_id = 0 OR tp.store_id = cs.store_id',
            ['website_id']
        )->where(
            'cs.store_id != 0'
        )->where(
            'e.type_id=?',
            $this->getTypeId()
        )->columns(
            new \Zend_Db_Expr('MIN(tp.value)')
        )->group(
            ["tp.{$linkField}", 'cg.customer_group_id', 'cs.store_id']
        );

        if (!empty($entityIds)) {
            $select->where("tp.{$linkField} IN(?)", $entityIds);
        }

        $query = $select->insertFromSelect($this->_getTierPriceIndexTable());
        $connection->query($query);

        return $this;
    }

    protected function _addWebsiteJoinToSelect($select, $store = true, $joinCondition = null)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::_addWebsiteJoinToSelect($select, $store, $joinCondition);
        }

        if ($joinCondition !== null) {
            $joinCondition = 'cw.website_id = ' . $joinCondition;
        }

        $select->join(['cw' => $this->getTable('store_website')], $joinCondition, []);

        if ($store) {
            $select->join(
                ['csg' => $this->getTable('store_group')],
                'csg.group_id = cw.default_group_id',
                []
            )->join(
                ['cs' => $this->getTable('store')],
                'cs.store_id != 0',
                []
            );
        }

        return $this;
    }
}