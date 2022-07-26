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
class Category 
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
	
	
	
	protected $_categoryCollectionFactory;
	protected $_categoryHelper;
	protected $_categoryFactory;

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
        QueryFactory $queryFactory,
		\Magento\Backend\Block\Template\Context $context, 
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Helper\Category $categoryHelper
    ) {
    
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_categoryFactory = $categoryFactory;
		$this->_storeManager = $storeManager;
		$this->_categoryHelper = $categoryHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryData()
    {
		$queryText = $this->queryFactory->get()->getQueryText();
		
		$collection = $this->getCategoryCollection()							
						   ->addFieldToFilter('is_active',1)						   
						   ->addAttributeToFilter(
												array(
													array('attribute'=> 'name','like' => '%'.$queryText.'%'),
																					
													)
												);
												
												
		
		$storeId= $this->_storeManager->getStore()->getId();		
		$rootCategoryId = $this->_storeManager->getStore($storeId)->getRootCategoryId();
		
		$collection->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"));
							
		$responseData = array();

		if(count($collection)>0){
		foreach ($collection as $category) { 		
		
			$path = $category->getPath();
			$ids = explode('/', $path);
			  if($this->helperData->getWithParentCategory()){
				  
				if (isset($ids[2])){					
					$parentCategoryName=$this->_category = $this->_categoryFactory->create()->load($ids[2]);					
					$catdata='<span>'.$parentCategoryName->getName().' > <span><a href="'.$category->getUrl().'">'.$category->getName().'</a>';
					
					$responseData['category'][] =array(
												"catdata"=> $catdata,
												"isSet"=> 1,											  
												);
				}else{					
					$responseData['category'][] =array(
												"catdata"=>'<a href="'.$category->getUrl().'">'.$category->getName().'</a>',
												"isSet"=> 1,											  
												);					
					
				}
				
			  } else{
				  
				  $responseData['category'][] =array(
												"catdata"=>'<a href="'.$category->getUrl().'">'.$category->getName().'</a>',
												"isSet"=> 1,											  
												);
				  
			  }			
												
			}
			
		}else{
			
			$responseData['category'][] =array(
												"catdata"=>'No Result',
												"isSet"=> 0,											   
												);
			
		}
		
	
		return $responseData;
		
		
    }
	
	
	public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
	{
		$queryText = $this->queryFactory->get()->getQueryText();
		
		 $collection = $this->_categoryCollectionFactory->create();
		 $collection->addAttributeToSelect('*'); 
		 
		 		 
		 // select only active categories
		 if ($isActive) {
		 $collection->addIsActiveFilter();
		 }
		 
		 // select categories of certain level
		 if ($level) {
		 $collection->addLevelFilter($level);
		 }
		 
		 // sort categories by some value
		 if ($sortBy) {
		 $collection->addOrderField($sortBy);
		 }
		 
		 // select certain number of categories
		 if ($pageSize) {
		 $collection->setPageSize($pageSize); 
		 } 
		 
				return $collection;
	}

    

    
}
