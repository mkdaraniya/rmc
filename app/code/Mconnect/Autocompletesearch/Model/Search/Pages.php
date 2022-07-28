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
class Pages 
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
	
	
	protected $_page;
	
	protected $pageHelper;
	
	
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
		\Magento\Cms\Model\Page $page,
		\Magento\Cms\Helper\Page $pageHelper,
        QueryFactory $queryFactory	
    ) {
    
        $this->helperData = $helperData;
        $this->searchHelper = $searchHelper;
        $this->layerResolver = $layerResolver;
        $this->objectManager = $objectManager;
        $this->queryFactory = $queryFactory;
		$this->_page = $page;
		$this->pageHelper = $pageHelper;
		
    }

    /**
     * {@inheritdoc}
     */
    public function getPagesData()
    {
		$queryText = $this->queryFactory->get()->getQueryText();
		
		$pagesSearch = $this->helperData->getPagesSearch();	
	
			
		if($pagesSearch){
			
			$pagesUniqueIdentifier = $this->helperData->getPagesUniqueIdentifier();	
			
			
			$pagesCollection=$this->_page->getCollection()
						//->addFieldToFilter('page_id', array('nin' => array(1)))
						//	->addFieldToFilter('identifier', array('nin' => array("about-us")))						
							->addFieldToFilter('is_active',1)
							->addFieldToFilter('identifier', array(array('nin'=>explode(',', $pagesUniqueIdentifier))))
							->addFieldToFilter(
											array('title','content'),
												array(
													array('like'=>'%'.$queryText.'%'), 
													array('like'=>'%'.$queryText.'%')
												)
											);
			
			
		}else{
		
			$pagesCollection=$this->_page->getCollection()
						  //->addFieldToFilter('page_id', array('nin' => array(1)))
						  //->addFieldToFilter('identifier', array('nin' => array('no-route')))		
							->addFieldToFilter('is_active',1)
							->addFieldToFilter(
											array('title','content'),
												array(
													array('like'=>'%'.$queryText.'%'), 
													array('like'=>'%'.$queryText.'%')
												)
											);
			
			
		}
		
										
											
		$responseData = array();
		if(count($pagesCollection)>0){
		foreach ($pagesCollection as $page) {	
		
			$responseData['pages'][] =array("title"=> $page->getTitle(),
											"pageUrl"=> $this->pageHelper->getPageUrl($page->getId()),
											"isSet"=> 1,
											);			
		}		
		}else{
			$responseData['pages'][] =array("title"=> 'No Result',
											"pageUrl"=> '#',
											"isSet"=> 0,
											);
		}
		return $responseData;
		
		
    }
	
	

    

    
}
