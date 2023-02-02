<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

use Magento\CatalogRule\Model\Indexer\IndexerTableSwapperInterface as TableSwapper;
use Magento\Catalog\Model\ResourceModel\Indexer\ActiveTableSwitcher;
use Magento\Framework\App\ObjectManager;

/**
 * Reindex rule relations with products.
 */
class ReindexRuleProduct extends \Magento\CatalogRule\Model\Indexer\ReindexRuleProduct
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var TableSwapper
     */
    private $tableSwapper;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $bshelper;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ActiveTableSwitcher $activeTableSwitcher
     * @param \Bss\MultiStoreViewPricing\Helper\Data $bshelper
     * @param TableSwapper|null $tableSwapper
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        ActiveTableSwitcher $activeTableSwitcher,
        \Bss\MultiStoreViewPricing\Helper\Data $bshelper,
        TableSwapper $tableSwapper = null
    ) {
        $this->resource = $resource;
        $this->bshelper = $bshelper;
        $this->tableSwapper = $tableSwapper ??
            ObjectManager::getInstance()->get(TableSwapper::class);
    }

    /**
     * Reindex information about rule relations with products.
     *
     * @param \Magento\CatalogRule\Model\Rule $rule
     * @param int $batchCount
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(
        \Magento\CatalogRule\Model\Rule $rule,
        $batchCount,
        $useAdditionalTable = false
    ) {

        if (!$rule->getIsActive() || empty($rule->getWebsiteIds())) {
            return false;
        }

        $connection = $this->resource->getConnection();
        $websiteIds = $rule->getWebsiteIds();
        if (!is_array($websiteIds)) {
            $websiteIds = explode(',', $websiteIds);
        }

        $indexTable = $this->resource->getTableName('catalogrule_product_store');
        if ($useAdditionalTable) {
            $indexTable = $this->resource->getTableName(
                $this->tableSwapper->getWorkingTableName('catalogrule_product_store')
            );
        }

        $ruleId = $rule->getId();
        $customerGroupIds = $rule->getCustomerGroupIds();
        $fromTime = $rule->getFromDate() !== null ? strtotime($rule->getFromDate()) : false;
        $toTime = $rule->getToDate() !== null ? strtotime($rule->getToDate()) : false;
        $toTime = $toTime ? $toTime + \Magento\CatalogRule\Model\Indexer\IndexBuilder::SECONDS_IN_DAY - 1 : 0;
        $sortOrder = (int)$rule->getSortOrder();
        $actionOperator = $rule->getSimpleAction();
        $actionAmount = $rule->getDiscountAmount();
        $actionStop = $rule->getStopRulesProcessing();

        $rows = [];
        foreach ($websiteIds as $websiteId) {
            \Magento\Framework\Profiler::start('__MATCH_PRODUCTS__');
            $rule->setWebsiteIds([$websiteId]);
            $productIds = $rule->getMatchingProductIds();
            \Magento\Framework\Profiler::stop('__MATCH_PRODUCTS__');
            foreach ($productIds as $productId => $validationByWebsite) {
                $website = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Store\Model\Website::class)->load($websiteId);
                $storeIds = $website->getStoreIds();
                foreach ($storeIds as $storeId) {
                    if (empty($validationByWebsite[$storeId])) {
                        continue;
                    }
                    foreach ($customerGroupIds as $customerGroupId) {
                        $rows[] = [
                            'rule_id' => $ruleId,
                            'from_time' => $fromTime,
                            'to_time' => $toTime,
                            'website_id' => $websiteId,
                            'store_id' => $storeId,
                            'customer_group_id' => $customerGroupId,
                            'product_id' => $productId,
                            'action_operator' => $actionOperator,
                            'action_amount' => $actionAmount,
                            'action_stop' => $actionStop,
                            'sort_order' => $sortOrder,
                        ];

                        if (count($rows) == $batchCount) {
                            $connection->insertMultiple($indexTable, $rows);
                            $rows = [];
                        }
                    }
                }
            }
        }
        if (!empty($rows)) {
            $connection->insertMultiple($indexTable, $rows);
        }
        return true;
    }
}
