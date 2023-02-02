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
 * @package    Bss_MultiStoreViewPricingCatalogRule
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\PriceModifierInterface;
use Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\IndexTableStructure;
use Magento\CatalogRule\Model\ResourceModel\Rule\Product\Price;
use Magento\Framework\App\ResourceConnection;

/**
 * Class for adding catalog rule price stores to price index table.
 */
class ProductPriceIndexModifier implements PriceModifierInterface
{
    /**
     * @var Price
     */
    private $priceResourceModel;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var string
     */
    private $connectionName;

    /**
     * @var string
     */
    private $bshelper;

    /**
     * @param Price $priceResourceModel
     * @param ResourceConnection $resourceConnection
     * @param \Bss\MultiStoreViewPricing\Helper\Data $bshelper
     * @param string $connectionName
     */
    public function __construct(
        Price $priceResourceModel,
        ResourceConnection $resourceConnection,
        \Bss\MultiStoreViewPricing\Helper\Data $bshelper,
        $connectionName = 'indexer'
    ) {
        $this->priceResourceModel = $priceResourceModel;
        $this->resourceConnection = $resourceConnection ?: ObjectManager::getInstance()->get(ResourceConnection::class);
        $this->connectionName = $connectionName;
        $this->bshelper = $bshelper;
    }

    /**
     * @inheritdoc
     */
    public function modifyPrice(IndexTableStructure $priceTable, array $entityIds = []) :void
    {
        $connection = $this->resourceConnection->getConnection($this->connectionName);

        $main_table = $this->priceResourceModel->getMainTable();
        $cpp_query = 'website_id';
        $i_query = $priceTable->getWebsiteField();  
        if ($this->bshelper->isScopePrice()) {
            $main_table = $this->priceResourceModel->getTable('catalogrule_product_price_store');
            $cpp_query = 'store_id';
            $i_query = 'store_id';
        }

        $select = $connection->select();

        $select->join(
            ['cpiw' => $this->priceResourceModel->getTable('catalog_product_index_website')],
            'cpiw.website_id = i.' . $priceTable->getWebsiteField(),
            []
        );
        $select->join(
            ['cpp' => $main_table],
            'cpp.product_id = i.' . $priceTable->getEntityField()
            . ' AND cpp.customer_group_id = i.' . $priceTable->getCustomerGroupField()
            . ' AND cpp.' . $cpp_query .' = i.' . $i_query
            . ' AND cpp.rule_date = cpiw.website_date',
            []
        );

        if ($entityIds) {
            $select->where('i.entity_id IN (?)', $entityIds);
        }

        $finalPrice = $priceTable->getFinalPriceField();
        $finalPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getFinalPriceField(),
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $finalPrice),
        ]);
        $minPrice = $priceTable->getMinPriceField();
        $minPriceExpr = $select->getConnection()->getLeastSql([
            $priceTable->getMinPriceField(),
            $select->getConnection()->getIfNullSql('cpp.rule_price', 'i.' . $minPrice),
        ]);
        $select->columns([
            $finalPrice => $finalPriceExpr,
            $minPrice => $minPriceExpr,
        ]);

        $query = $connection->updateFromSelect($select, ['i' => $priceTable->getTableName()]);
        $connection->query($query);
    }
}
