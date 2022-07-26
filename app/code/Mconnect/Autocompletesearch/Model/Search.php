<?php
namespace Mconnect\Autocompletesearch\Model;


use Magento\Framework\ObjectManagerInterface as ObjectManager;

/**
 * Search class returns needed search data
 * @package Mconnect\Autocompletesearch\Model
 */
class Search
{
    /**
     * @var \Mconnect\Autocompletesearch\Model\SearchFactory
     */
    //  protected $searchFactory;

    /**
     * Search constructor.
     * @param \Mconnect\Autocompletesearch\Model\SearchFactory $searchFactory
     */
    protected $objectManager = null;

    /**
     * Search constructor.
     * @param \Mconnect\Autocompletesearch\Model\SearchFactory $searchFactory
     */
    public function __construct(
		ObjectManager $objectManager      
    ) {    
		$this->objectManager = $objectManager; 		
    }
    

    /**
     * Retrieve products
     *
     * @return array
     */
    public function getProducts()
    {
		return  $this->objectManager->create('\Mconnect\Autocompletesearch\Model\Search\Product')->getResponseData();
		
    }
	
	public function getCategory()
    {
		return  $this->objectManager->create('\Mconnect\Autocompletesearch\Model\Search\Category')->getCategoryData();
		
    }
	
	
	public function getPages()
    {
		return  $this->objectManager->create('\Mconnect\Autocompletesearch\Model\Search\Pages')->getPagesData();
		
    }
	
	
	
}
