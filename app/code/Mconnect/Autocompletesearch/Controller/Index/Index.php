<?php
namespace  Mconnect\Autocompletesearch\Controller\Index;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\Controller\ResultFactory;

use \Mconnect\Autocompletesearch\Model\Search as SearchModel;


use \Mconnect\Autocompletesearch\Helper\Data as HelperData;




class Index extends Action
{
	private $autocomplete;
	
	/**
     * @var \Mconnect\Autocompletesearch\Model\Search
     */
    private $searchModel;
	
	/**
     * @var \Mconnect\Autocompletesearch\Helper\Data
     */
	 
	
	 
    protected $helperData;
	
	
	
	protected $layerResolver;
	
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
	

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Search\Model\AutocompleteInterface $autocomplete
     */
    public function __construct(
        Context $context,
		HelperData $helperData,			
        AutocompleteInterface $autocomplete,
		SearchModel $searchModel	
    ) {
        $this->autocomplete = $autocomplete;
		$this->helperData = $helperData;		
		$this->searchModel = $searchModel;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		
		$q=$this->getRequest()->getParam('q', false);
		if(!empty($q)){ 
		
		$pagesData =array();
		if($this->helperData->getPagesSearch()){
		$pagesData = $this->searchModel->getPages();	
		}
				
		$categoryData =array();
		if($this->helperData->getCategorySearch()){
		$categoryData = $this->searchModel->getCategory();
		}
		
		$productsData = $this->searchModel->getProducts();			
		
		$responseData = [];
		
			$responseData = array_merge(
                $pagesData,
                $categoryData,
                $productsData
            );
			/*
			$responseData = array_merge(
                $responseData,
                $pagesData
                
            );
			*/
				
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);      

        $resultJson->setData($responseData);
        return $resultJson;
		
		
		}
	}		
	
}