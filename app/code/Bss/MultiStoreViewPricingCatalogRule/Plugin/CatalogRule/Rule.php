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
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_MultiStoreViewPricingCatalogRule
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Plugin\CatalogRule;

class Rule
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    public $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule $subject
     * @param \Closure $proceed
     * @param \DateTimeInterface $date
     * @param $websiteId
     * @param $customerGroupId
     * @param $productIds
     * @return mixed
     */
    public function aroundGetRulePrices(\Magento\CatalogRule\Model\ResourceModel\Rule $subject, \Closure $proceed, \DateTimeInterface $date, $websiteId, $customerGroupId, $productIds)
    {
        if (!$this->helper->isScopePrice()) {
            $result = $proceed($date, $websiteId, $customerGroupId, $productIds);
            return $result;
        }

        $currentStoreId = $this->storeManager->getStore()->getId();

        $connection = $subject->getConnection();
        $select = $connection->select()
            ->from($subject->getTable('catalogrule_product_price_store'), ['product_id', 'rule_price'])
            ->where('rule_date = ?', $date->format('Y-m-d'))
            ->where('store_id = ?', $currentStoreId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id IN(?)', $productIds);

        return $connection->fetchPairs($select);
    }

    /**
     * @param \Magento\CatalogRule\Model\ResourceModel\Rule $subject
     * @param \Closure $proceed
     * @param $date
     * @param $websiteId
     * @param $customerGroupId
     * @param $productId
     * @return mixed
     */
    public function aroundGetRulesFromProduct(\Magento\CatalogRule\Model\ResourceModel\Rule $subject, \Closure $proceed, $date, $websiteId, $customerGroupId, $productId)
    {
        if (!$this->helper->isScopePrice()) {
            $result = $proceed($date, $websiteId, $customerGroupId, $productIds);
            return $result;
        }

        $currentStoreId = $this->storeManager->getStore()->getId();
        $connection = $subject->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection->select()
            ->from($subject->getTable('catalogrule_product_store'))
            ->where('website_id = ?', $websiteId)
            ->where('store_id = ?', $currentStoreId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date);

        return $connection->fetchAll($select);
    }
}
