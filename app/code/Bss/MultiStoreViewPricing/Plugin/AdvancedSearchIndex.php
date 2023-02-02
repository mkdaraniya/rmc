<?php
namespace Bss\MultiStoreViewPricing\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Catalog\Model\Indexer\Product\Price\DimensionCollectionFactory;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

class AdvancedSearchIndex
{
    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var DimensionCollectionFactory|null
     */
    private $dimensionCollectionFactory;

    /**
     * @var int|null
     */
    private $websiteId;

    /**
     * ValidationMessage constructor.
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper,
        \Magento\Framework\App\ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        IndexScopeResolverInterface $tableResolver = null,
        DimensionCollectionFactory $dimensionCollectionFactory = null
    ) {
        $this->helper = $helper;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->tableResolver = $tableResolver ?: ObjectManager::getInstance()->get(IndexScopeResolverInterface::class);
        $this->dimensionCollectionFactory = $dimensionCollectionFactory
            ?: ObjectManager::getInstance()->get(DimensionCollectionFactory::class);
    }

    /**
     * @param $subject
     * @param $proceed
     * @param $productIds
     * @param $storeId
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundGetPriceIndexData($subject, $proceed, $productIds, $storeId)
    {
        if (!$this->helper->isScopePrice()) {
            return $proceed($productIds, $storeId);
        }

        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();

        $this->websiteId = $websiteId;
        $priceProductsIndexData = $this->_getCatalogProductPriceData($productIds);
        $this->websiteId = null;

        if (!isset($priceProductsIndexData[$websiteId])) {
            return [];
        }

        return $priceProductsIndexData[$websiteId][$storeId];

    }

    /**
     * @param null $productIds
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    protected function _getCatalogProductPriceData($productIds = null)
    {
        $connection = $this->resource->getConnection();
        $catalogProductIndexPriceSelect = [];
        foreach ($this->dimensionCollectionFactory->create() as $dimensions) {
            if (!isset($dimensions[WebsiteDimensionProvider::DIMENSION_NAME]) ||
                $this->websiteId === null ||
                $dimensions[WebsiteDimensionProvider::DIMENSION_NAME]->getValue() === $this->websiteId) {
                $select = $connection->select()->from(
                    $this->tableResolver->resolve('catalog_product_index_price_store', $dimensions),
                    ['entity_id', 'customer_group_id', 'website_id', 'store_id', 'min_price']
                );
                if ($productIds) {
                    $select->where('entity_id IN (?)', $productIds);
                }
                $catalogProductIndexPriceSelect[] = $select;
            }
        }
        $catalogProductIndexPriceUnionSelect = $connection->select()->union($catalogProductIndexPriceSelect);
        $result = [];
        foreach ($connection->fetchAll($catalogProductIndexPriceUnionSelect) as $row) {
            $result[$row['website_id']][$row['store_id']][$row['entity_id']][$row['customer_group_id']] = round($row['min_price'], 2);
        }
        return $result;
    }
}
