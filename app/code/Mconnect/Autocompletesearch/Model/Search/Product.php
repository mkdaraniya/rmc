<?php
namespace Mconnect\Autocompletesearch\Model\Search;

use \Mconnect\Autocompletesearch\Helper\Data as HelperData;

use \Magento\Search\Helper\Data as SearchHelper;
use \Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use \Magento\Search\Model\QueryFactory;

use \Mconnect\Autocompletesearch\Model\Source\ProductFields;

/**
 * Product model. Return product data used in search autocomplete
 * @package Mconnect\Autocompletesearch\Model\Search
 */
class Product 
{
    /**
     * @var \Mconnect\Autocompletesearch\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Search\Helper\Data
     */
    protected $searchHelper;

    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    protected $layerResolver;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Search\Model\QueryFactory
     */
    private $queryFactory;

    /**
     * Product constructor.
     *
     * @param HelperData $helperData
     * @param SearchHelper $searchHelper
     * @param LayerResolver $layerResolver
     * @param ObjectManager $objectManager
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        HelperData $helperData,
        SearchHelper $searchHelper,
        LayerResolver $layerResolver,
        ObjectManager $objectManager,
        QueryFactory $queryFactory
    ) {
    
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseData()
    {
        $queryText = $this->queryFactory->get()->getQueryText();
		
        $productResultFields = $this->helperData->getProductResultFieldsAsArray();
        
        $productResultFields[] = ProductFields::URL;
        
        $productResultNumber = $this->helperData->getProductResultNumber();		
		
		$responseData['products']['data'] = [];
		
		$this->layerResolver->create(LayerResolver::CATALOG_LAYER_SEARCH);
		
		$productCollection = $this->layerResolver->get()
            ->getProductCollection()
            ->addAttributeToSelect(['description', 'short_description'])
            ->addAttributeToFilter(
                [
                 ['attribute' => 'name', 'like' => '%'.$queryText.'%'],
                 ['attribute' => 'sku', 'like' => '%'.$queryText.'%']
                ])
            ->addSearchFilter($queryText);

        $productCollection->getSelect()->limit($productResultNumber);
		
		foreach ($productCollection as $product) {
			
           // $responseData['products']['data'][] = $this->getProductData($product);
		   $responseData['products']['data'][] = array_intersect_key($this->getProductData($product), array_flip($productResultFields));
        }
		
		$responseData['products']['size'] = $productCollection->getSize();
        $responseData['products']['url'] = ($productCollection->getSize() > 0) ? $this->searchHelper->getResultUrl($queryText) : '';
		
		 return $responseData;
		
    }

    /**
     * Retrieve all product data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function getProductData($product)
    {
        /** @var \Mconnect\Autocompletesearch\Block\Autocomplete\Product $product */
        $product = $this->objectManager->create('Mconnect\Autocompletesearch\Block\Autocomplete\Product')
            ->setProduct($product);
			

        $name = $product->getName();
		$sku = $product->getSku();
        $smallImage = $product->getSmallImage();
        $reviewsRating = $product->getReviewsRating();
		$description = $product->getDescription();
		$price = $product->getPrice();		
		$url = $product->getUrl();
		$addToCart = $product->getAddToCartData();
       

        $data = [
            ProductFields::NAME => $name,
			ProductFields::SKU => $sku,
            ProductFields::IMAGE => $smallImage,
            ProductFields::REVIEWS_RATING => $reviewsRating,
			ProductFields::DESCRIPTION => $description,
			ProductFields::PRICE => $price,
            ProductFields::ADD_TO_CART => $addToCart,
            ProductFields::URL => $url,
            
        ];

        return $data;
    }

    
}
