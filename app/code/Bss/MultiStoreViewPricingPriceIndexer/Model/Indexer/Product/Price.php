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
namespace Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Framework\Indexer\CacheContext;

class Price extends \Magento\Catalog\Model\Indexer\Product\Price
{
    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Row
     */
    protected $_productPriceIndexerRow;

    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Rows
     */
    protected $_productPriceIndexerRows;

    /**
     * @var \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Full
     */
    protected $_productPriceIndexerFull;

    /**
     * @var \Magento\Framework\Indexer\CacheContext
     */
    protected $cacheContext;

    /**
     * @param Price\Action\Row $productPriceIndexerRow
     * @param Price\Action\Rows $productPriceIndexerRows
     * @param Price\Action\Full $productPriceIndexerFull
     * @param CacheContext $cacheContext
     */
    public function __construct(
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Row $productPriceIndexerRow,
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Rows $productPriceIndexerRows,
        \Bss\MultiStoreViewPricingPriceIndexer\Model\Indexer\Product\Price\Action\Full $productPriceIndexerFull,
        CacheContext $cacheContext
    ) {
        $this->_productPriceIndexerRow = $productPriceIndexerRow;
        $this->_productPriceIndexerRows = $productPriceIndexerRows;
        $this->_productPriceIndexerFull = $productPriceIndexerFull;
        $this->cacheContext = $cacheContext;
    }


    /**
     * Execute materialization on ids entities
     *
     * @param int[] $ids
     * @return void
     */
    public function execute($ids)
    {
        $this->_productPriceIndexerRows->execute($ids);
        $this->cacheContext->registerEntities(ProductModel::CACHE_TAG, $ids);
    }

    /**
     * Execute full indexation
     *
     * @return void
     */
    public function executeFull()
    {
        $this->_productPriceIndexerFull->execute();
        $this->cacheContext->registerTags(
            [
                CategoryModel::CACHE_TAG,
                ProductModel::CACHE_TAG
            ]
        );
    }

    /**
     * Execute partial indexation by ID list
     *
     * @param int[] $ids
     * @return void
     */
    public function executeList(array $ids)
    {
        $this->_productPriceIndexerRows->execute($ids);
        $this->cacheContext->registerEntities(ProductModel::CACHE_TAG, $ids);
    }

    /**
     * Execute partial indexation by ID
     *
     * @param int $id
     * @return void
     */
    public function executeRow($id)
    {
        $this->_productPriceIndexerRow->execute($id);
        $this->cacheContext->registerEntities(ProductModel::CACHE_TAG, [$id]);
    }
}
