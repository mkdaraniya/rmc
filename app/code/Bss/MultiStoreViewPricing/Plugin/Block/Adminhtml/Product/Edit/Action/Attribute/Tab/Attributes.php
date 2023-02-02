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
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Plugin\Block\Adminhtml\Product\Edit\Action\Attribute\Tab;

/**
 * Class Attributes
 *
 * @package Bss\MultiStoreViewPricing\Plugin\Block\Adminhtml\Product\Edit\Action\Attribute\Tab
 */
class Attributes
{
    /**
     * @var \Magento\Framework\App\Request\Http $request
     */
    protected $request;

	/**
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Around set form, set store id to form.
     *
     * @param \Magento\Catalog\Block\Adminhtml\Product\Edit\Action\Attribute\Tab\Attributes $subject
     * @return \Magento\Framework\Data\Form
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetForm($subject, $proceed, \Magento\Framework\Data\Form $form)
    {
    	$storeId = $this->request->getParam('store');
    	if ($form->getDataObject()) {
    		$form->getDataObject()->setStoreId($storeId);
    	}
        return $proceed($form);
    }
}
