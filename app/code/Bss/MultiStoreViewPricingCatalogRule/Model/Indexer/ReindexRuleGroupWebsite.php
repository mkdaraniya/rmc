<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;

/**
 * Reindex information about rule relations with customer groups and websites.
 */
class ReindexRuleGroupWebsite extends \Magento\CatalogRule\Model\Indexer\ReindexRuleGroupWebsite
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var array
     */
    private $catalogRuleGroupWebsiteColumnsList = ['rule_id', 'customer_group_id', 'website_id'];

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $bshelper;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param \Bss\MultiStoreViewPricing\Helper\Data $bshelper
     * @param TableSwapper|null $tableSwapper
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        \Bss\MultiStoreViewPricing\Helper\Data $bshelper,
        TableSwapper $tableSwapper = null
    ) {
        $this->dateTime = $dateTime;
        $this->resource = $resource;
        $this->bshelper = $bshelper;
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Prepare and persist information about rule relations with customer groups and websites to index table.
     *
     * @param bool $useAdditionalTable
     * @return bool
     */
    public function execute($useAdditionalTable = false)
    {
        $connection = $this->resource->getConnection();
        $timestamp = $this->dateTime->gmtTimestamp();

        $indexTable = $this->resource->getTableName('catalogrule_group_website');
        $ruleProductTable = $this->resource->getTableName('catalogrule_product_store');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_group_website')
            );
            $ruleProductTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product_store')
            );
        }

        $connection->delete($indexTable);
        $select = $connection->select()->distinct(
            true
        )->from(
            $ruleProductTable,
            $this->catalogRuleGroupWebsiteColumnsList
        )->where(
            "{$timestamp} >= from_time AND (({$timestamp} <= to_time AND to_time > 0) OR to_time = 0)"
        );
        $query = $select->insertFromSelect($indexTable, $this->catalogRuleGroupWebsiteColumnsList);
        $connection->query($query);
        return true;
    }
}
