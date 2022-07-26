<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Mconnect\Autocompletesearch\Block;



/**
 * Autocomplete class used to paste config data
 * @package Mconnect\Autocompletesearch\Block
 */
class Autocomplete extends \Magento\Framework\View\Element\Template
{
	/**
     * Autocomplete constructor.
     *
     * @param \Mconnect\Autocompletesearch\Helper\Data $helperData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
	 public function __construct(
        \Mconnect\Autocompletesearch\Helper\Data $helperData,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
    
        $this->helperData = $helperData;
        parent::__construct($context, $data);
    }
	
	
	 /**
	 * Retrieve search action url
	 *
	 * @return string
	 */
	
	public function getSearchUrl()
    {
        return $this->getUrl("autocompletesearch/index/index");
    }
	/*
	public function getBaseUrl()
    {
        return $this->getUrl();
    }
   */
	
	public function getFullPathLodingImage()
    {
        $midImageUrl=$this->getUrl();		
	    return	$midImageUrl.'pub/media/mconnect/autocompletesearch/'.$this->getLodingImage();
		
    }
   
   
    /*
	 * Min Latter Number
     *
     * @return int
     */
	 
    public function getProductMinLatterNumber()
    {
        return $this->helperData->getProductMinLatterNumber();
    }
	
	/*
     * get Loding Image
     *
     * @return int
     */
	 
    public function getLodingImage()
    {
        return $this->helperData->getLodingImage();
    }
	
	/*
     * get getViewAllLinkStatus    
     * @return int
	 *
     */
	 
    public function getViewAllLinkStatus()
    {
        return $this->helperData->getViewAllLinkStatus();
    }
	
	/*
     * get getViewAllLinkText    
     * @return int
	 *
     */
	 
    public function getViewAllLinkText()
    {
        return $this->helperData->getViewAllLinkText();
    }
	
	/*
     * get getCountSearchResults   
     * @return int
	 *
     */
	 
    public function getCountSearchResults()
    {
        return $this->helperData->getCountSearchResults();
    }
	
	
	
}
