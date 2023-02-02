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
 * @package   Bss_MultiStoreViewPricing
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Model\ResourceModel;

use Magento\Catalog\Model\Product;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\ObjectManager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RuleAdmin extends \Magento\CatalogRule\Model\ResourceModel\Rule
{   

    /**
     * @param \Magento\Framework\Model\AbstractModel $rule
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $rule)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable('catalogrule_product_store'),
            ['rule_id=?' => $rule->getId()]
        );
        $connection->delete(
            $this->getTable('catalogrule_group_website'),
            ['rule_id=?' => $rule->getId()]
        );
        return parent::_afterDelete($rule);
    }

    /**
     * Get catalog rules product price for specific date, website and
     * customer group
     *
     * @param  \DateTime $date
     * @param  int       $wId
     * @param  int       $gId
     * @param  int       $pId
     * @return float|false
     */
    public function getRulePrice($date, $wId, $gId, $pId, $sId = false)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::getRulePrice($date, $wId, $gId, $pId);
        }

        if (!$sId) {
            return false;
        }
        
        $data = $this->getRulePrices($date, $wId, $gId, [$pId], $sId);
        if (isset($data[$pId])) {
            return $data[$pId];
        }

        return false;
    }

    /**
     * Retrieve product prices by catalog rule for specific date, website and customer group
     * Collect data with  product Id => price pairs
     *
     * @param  \DateTime $date
     * @param  int       $websiteId
     * @param  int       $customerGroupId
     * @param  array     $productIds
     * @return array
     */
    public function getRulePrices(\DateTimeInterface $date, $websiteId, $customerGroupId, $productIds, $storeId = null)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::getRulePrices($date, $websiteId, $customerGroupId, $productIds);
        }
        
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('catalogrule_product_price_store'),
            ['product_id', 'rule_price']
        )->where(
            'rule_date = ?',
            $date->format('Y-m-d')
        )->where(
            'store_id = ?',
            $storeId
        )->where(
            'customer_group_id = ?',
            $customerGroupId
        )->where(
            'product_id IN(?)',
            $productIds
        );
        return $connection->fetchPairs($select);
    }

    /**
     * Get active rule data based on few filters
     *
     * @param int|string $date
     * @param int $websiteId
     * @param int $customerGroupId
     * @param int $productId
     * @return array
     */
    public function getRulesFromProduct($date, $websiteId, $customerGroupId, $productId)
    {
        $helper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$helper->isScopePrice()) {
            return parent::getRulesFromProduct($date, $websiteId, $customerGroupId, $productIds);
        }

        $website = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Store\Model\Website::class)->load($websiteId);
        $defaultGroup = $website->getDefaultGroup();
        if ($defaultGroup instanceof \Magento\Store\Model\Group) {
            $storeId = $defaultGroup->getDefaultStoreId();
        } else {
            $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        }
        $connection = $this->getConnection();
        if (is_string($date)) {
            $date = strtotime($date);
        }
        $select = $connection->select()
            ->from($this->getTable('catalogrule_product_store'))
            ->where('website_id = ?', $websiteId)
            ->where('store_id = ?',$storeId)
            ->where('customer_group_id = ?', $customerGroupId)
            ->where('product_id = ?', $productId)
            ->where('from_time = 0 or from_time < ?', $date)
            ->where('to_time = 0 or to_time > ?', $date);

        return $connection->fetchAll($select);
    }
}
