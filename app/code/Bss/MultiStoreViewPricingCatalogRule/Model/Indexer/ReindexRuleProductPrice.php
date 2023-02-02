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
namespace Bss\MultiStoreViewPricingCatalogRule\Model\Indexer;

/**
 * Reindex product prices according rule settings.
 */
class ReindexRuleProductPrice extends \Magento\CatalogRule\Model\Indexer\ReindexRuleProductPrice
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder
     */
    private $ruleProductsSelectBuilder;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator
     */
    private $productPriceCalculator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor
     */
    private $pricesPersistor;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * ReindexRuleProductPrice constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder $ruleProductsSelectBuilder
     * @param \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator $productPriceCalculator
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor $pricesPersistor
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogRule\Model\Indexer\RuleProductsSelectBuilder $ruleProductsSelectBuilder,
        \Magento\CatalogRule\Model\Indexer\ProductPriceCalculator $productPriceCalculator,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\CatalogRule\Model\Indexer\RuleProductPricesPersistor $pricesPersistor,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        parent::__construct(
            $storeManager,
            $ruleProductsSelectBuilder,
            $productPriceCalculator,
            $localeDate,
            $pricesPersistor
        );
        $this->storeManager = $storeManager;
        $this->ruleProductsSelectBuilder = $ruleProductsSelectBuilder;
        $this->productPriceCalculator = $productPriceCalculator;
        $this->localeDate = $localeDate;
        $this->pricesPersistor = $pricesPersistor;
        $this->helper = $helper;
    }

    /**
     * Reindex product prices.
     *
     * @param int $batchCount
     * @param \Magento\Catalog\Model\Product|null $product
     * @param bool $useAdditionalTable
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @throws \Exception
     */
    public function execute(
        int $batchCount,
        ?int $product = null,
        bool $useAdditionalTable = false
    ) {
        if (!$this->helper->isScopePrice()) {
            return parent::execute($batchCount, $product, $useAdditionalTable);
        }

        $fromDate = mktime(0, 0, 0, date('m'), date('d') - 1);
        $toDate = mktime(0, 0, 0, date('m'), date('d') + 1);

        foreach ($this->storeManager->getWebsites() as $website) {
            foreach ($website->getGroups() as $group) {
                $stores = $group->getStores();
                foreach ($stores as $store) {
                    $productsStmt = $this->ruleProductsSelectBuilder->build(
                        $website->getId(),
                        $product,
                        $useAdditionalTable,
                        $store->getId()
                    );

                    $dayPrices = [];
                    $stopFlags = [];
                    $prevKey = null;

                    $storeGroup = $this->storeManager->getGroup($website->getDefaultGroupId());
                    $currentDate = $this->localeDate->scopeDate($storeGroup->getDefaultStoreId(), null, true);
                    $previousDate = (clone $currentDate)->modify('-1 day');
                    $previousDate->setTime(23, 59, 59);
                    $nextDate = (clone $currentDate)->modify('+1 day');
                    $nextDate->setTime(0, 0, 0);

                    while ($ruleData = $productsStmt->fetch()) {
                        $ruleProductId = $ruleData['product_id'];
                        $ruleData['store_id'] = $store->getId();
                        $productKey = $ruleProductId .
                            '_' .
                            $ruleData['website_id'] .
                            '_' .
                            $ruleData['customer_group_id'] .
                            '_' .
                            $store->getId();

                        if ($prevKey && $prevKey != $productKey) {
                            $stopFlags = [];
                            if (count($dayPrices) > $batchCount) {
                                $this->pricesPersistor->execute($dayPrices, $useAdditionalTable);
                                $dayPrices = [];
                            }
                        }

                        /**
                         * Build prices for each day
                         */
                        foreach ([$previousDate, $currentDate, $nextDate] as $date) {
                            $time = $date->getTimestamp();
                            if (($ruleData['from_time'] == 0 ||
                                    $time >= $ruleData['from_time']) && ($ruleData['to_time'] == 0 ||
                                    $time <= $ruleData['to_time'])
                            ) {
                                $priceKey = $time . '_' . $productKey;

                                if (isset($stopFlags[$priceKey])) {
                                    continue;
                                }

                                if (!isset($dayPrices[$priceKey])) {
                                    $dayPrices[$priceKey] = [
                                        'rule_date' => $date,
                                        'store_id' => $ruleData['store_id'],
                                        'customer_group_id' => $ruleData['customer_group_id'],
                                        'product_id' => $ruleProductId,
                                        'rule_price' => $this->productPriceCalculator->calculate($ruleData),
                                        'latest_start_date' => $ruleData['from_time'],
                                        'earliest_end_date' => $ruleData['to_time'],
                                    ];
                                } else {
                                    $dayPrices[$priceKey]['rule_price'] = $this->productPriceCalculator->calculate(
                                        $ruleData,
                                        $dayPrices[$priceKey]
                                    );
                                    $dayPrices[$priceKey]['latest_start_date'] = max(
                                        $dayPrices[$priceKey]['latest_start_date'],
                                        $ruleData['from_time']
                                    );
                                    $dayPrices[$priceKey]['earliest_end_date'] = min(
                                        $dayPrices[$priceKey]['earliest_end_date'],
                                        $ruleData['to_time']
                                    );
                                }

                                if ($ruleData['action_stop']) {
                                    $stopFlags[$priceKey] = true;
                                }
                            }
                        }
                        $prevKey = $productKey;
                    }
                    $this->pricesPersistor->execute($dayPrices, $useAdditionalTable);
                }
            }
        }
        return true;
    }
}
