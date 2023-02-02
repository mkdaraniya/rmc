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
 * @category  BSS
 * @package   Bss_MultiStoreViewPricingCatalogRule
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Plugin\ConfigurableProduct\Model\ResourceModel;

use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Product\Collection;
use Magento\CatalogRule\Pricing\Price\CatalogRulePrice;

class AddCatalogRulePrice
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->resource = $resourceConnection;
        $this->customerSession = $customerSession;
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * Add Catalog rule store data.
     *
     * @param Collection $productCollection
     * @param bool $printQuery
     * @param bool $logQuery
     * @return array
     */
    public function beforeLoad(Collection $productCollection, $printQuery = false, $logQuery = false)
    {
        if ($this->helper->isScopePrice()) {
            try {
                if (!$productCollection->hasFlag('catalog_rule_loaded')) {
                    $this->addCatalogRulePriceData($productCollection);
                } else {
                    $this->switchCatalogRulePrice($productCollection);
                }
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }

        return [$printQuery, $logQuery];
    }

    /**
     * Add Catalog rule price to collection.
     *
     * @param Collection $productCollection
     */
    protected function addCatalogRulePriceData($productCollection)
    {
        $connection = $this->resource->getConnection();
        $productCollection->getSelect()
            ->joinLeft(
                ['catalog_rule' => $this->resource->getTableName('catalogrule_product_price_store')],
                implode(' AND ', [
                    'catalog_rule.product_id = e.entity_id',
                    $connection->quoteInto('catalog_rule.store_id = ?', $productCollection->getStoreId()),
                    $connection->quoteInto(
                        'catalog_rule.customer_group_id = ?',
                        $this->customerSession->getCustomerGroupId()
                    ),
                    $connection->quoteInto(
                        'catalog_rule.rule_date = ?',
                        $this->dateTime->formatDate(
                            $this->localeDate->scopeDate($productCollection->getStoreId()),
                            false
                        )
                    ),
                ]),
                [CatalogRulePrice::PRICE_CODE => 'rule_price']
            );
        $productCollection->setFlag('catalog_rule_loaded', true);
    }

    /**
     * Switch Catalog rule price to Catalog rule price store.
     *
     * @param Collection $productCollection
     * @throws \Zend_Db_Select_Exception
     */
    protected function switchCatalogRulePrice($productCollection)
    {
        $fromPart = $productCollection->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
        if (isset($fromPart['catalog_rule'])) {
            $indexTable = $productCollection->getTable('catalogrule_product_price');
            $indexTableStore = $productCollection->getTable('catalogrule_product_price_store');

            $select = $productCollection->getSelect();

            if ($fromPart['catalog_rule']['tableName'] == $indexTable) {
                $fromPart['catalog_rule']['tableName'] = $indexTableStore;

                if (isset($fromPart['catalog_rule']['joinCondition'])) {
                    $condition = explode(' AND ', $fromPart['catalog_rule']['joinCondition']);

                    foreach ($condition as $i => $item) {
                        if (strpos($item, 'catalog_rule.website_id') !== false) {
                            $condition[$i] = 'catalog_rule.store_id = ' . $productCollection->getStoreId();
                        }
                    }
                    $condition = implode(' AND ', $condition);
                    $fromPart['catalog_rule']['joinCondition'] = $condition;
                }

                $select->setPart(\Magento\Framework\DB\Select::FROM, $fromPart);
            }
        }
    }
}
