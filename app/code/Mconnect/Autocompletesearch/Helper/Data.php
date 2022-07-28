<?php
namespace Mconnect\Autocompletesearch\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Search Suite Autocomplete config data helper
 * @package Mconnect\Autocompletesearch\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * XML config path product results number
     */
    const XML_PATH_PRODUCT_RESULT_NUMBER = 'mconnect_autocompletesearch/general/product_result_number';
	
	
	 /**
     * XML config path product result fields  
     */
    const XML_PATH_PRODUCT_RESULT_FIELDS   = 'mconnect_autocompletesearch/general/product_result_fields';
	
	/**
     * XML config path product min latter number
     */
    const XML_PATH_PRODUCT_MIN_LATTER_NUMBER  = 'mconnect_autocompletesearch/general/min_latter_number';
	
	/**
     * XML config path loading_image
     */
    const XML_PATH_PRODUCT_LOADING_IMAGE  = 'mconnect_autocompletesearch/general/loading_image';
	
	/**
     * XML config path view_all_link
     */
    const XML_PATH_PRODUCT_VIEW_ALL_LINK  = 'mconnect_autocompletesearch/general/view_all_link';
	
	/**
     * XML config path view_all_link_text
     */
    const XML_PATH_PRODUCT_VIEW_ALL_LINK_TEXT  = 'mconnect_autocompletesearch/general/view_all_link_text';
	
	/**
     * XML config path count_search_results
     */
    const XML_PATH_PRODUCT_COUNT_SEARCH_RESULTS  = 'mconnect_autocompletesearch/general/count_search_results';
	
	/**
     * XML config path pages_search
     */
    const XML_PATH_PRODUCT_PAGES_SEARCH  = 'mconnect_autocompletesearch/general/pages_search';
	
	
	/**
     * XML config path pages_unique_identifier
     */
    const XML_PATH_PRODUCT_PAGES_UNIQUE_IDENTIFIER = 'mconnect_autocompletesearch/general/pages_unique_identifier';
	
	/**
     * XML config path category_search
     */
    const XML_PATH_PRODUCT_CATEGORY_SEARCH = 'mconnect_autocompletesearch/general/category_search';
	
	
	/**
     * XML config path category_search
     */
    const XML_PATH_PRODUCT_WITH_PARENT_CATOGORY = 'mconnect_autocompletesearch/general/with_parent_category';
	
	
	public function getConfig($configPath) {
			return $this->scopeConfig->getValue(
				$configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
			);
		}
	
	/**
     * Retrieve Loading Image
     *     
     */
    public function getLodingImage()
    {
        return $this->getConfig(self::XML_PATH_PRODUCT_LOADING_IMAGE);
		/*return $this->scopeConfig->getValue(
            self::XML_PATH_PRODUCT_LOADING_IMAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );*/
    }
	    
	/**
     * Retrieve product min latter number
     *
     * @param int|null $storeId
     * @return int
     */
    public function getProductMinLatterNumber($storeId = null)
    {
		return $this->getConfig(self::XML_PATH_PRODUCT_MIN_LATTER_NUMBER);        
    }
	
	
    /**
     * Retrieve product results number
     *
     * @param int|null $storeId
     * @return int
     */
    public function getProductResultNumber($storeId = null)
    {
		return $this->getConfig(self::XML_PATH_PRODUCT_RESULT_NUMBER);        
        
    }
	
	
	/**
     * Retrieve comma-separated product result fields
     *
     * @param int|null $storeId
     * @return string
     */
    public function getProductResultFields()
    {
		return $this->getConfig(self::XML_PATH_PRODUCT_RESULT_FIELDS);        
        
    }

    /**
     * Retrieve list of product result fields
     *
     * @param int|null $storeId
     * @return array
     */
    public function getProductResultFieldsAsArray()
    {
        return explode(',', $this->getProductResultFields());
    }
	
	
	
	public function getViewAllLinkStatus()
    {	
		return $this->getConfig(self::XML_PATH_PRODUCT_VIEW_ALL_LINK);     
         
    }
	
	
	public function getViewAllLinkText()
    {	
		return $this->getConfig(self::XML_PATH_PRODUCT_VIEW_ALL_LINK_TEXT);     
         
    }
	
	public function getCountSearchResults()
    {
		return $this->getConfig(self::XML_PATH_PRODUCT_COUNT_SEARCH_RESULTS);     
        
    }
	
	/**
     * pages search enable/disable
     *    
     **/
	
	public function getPagesSearch()
    {	
		return $this->getConfig(self::XML_PATH_PRODUCT_PAGES_SEARCH); 
        
    }
	
	/**
     * pages unique indetifier
     *    
     **/
	
	public function getPagesUniqueIdentifier()
    {
		return $this->getConfig(self::XML_PATH_PRODUCT_PAGES_UNIQUE_IDENTIFIER); 		
         
    }
	
	/**
     * category search enable/disable
     *    
     **/
	
	public function getCategorySearch()
    {	
		return $this->getConfig(self::XML_PATH_PRODUCT_CATEGORY_SEARCH); 		
        
    }
	
	/**
     * WITH PARENT CATOGORY enable/disable
     *    
     **/
	
	public function getWithParentCategory()
    {	
		return $this->getConfig(self::XML_PATH_PRODUCT_WITH_PARENT_CATOGORY); 		
        
    }
}
