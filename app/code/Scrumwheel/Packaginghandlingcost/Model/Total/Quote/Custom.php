<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scrumwheel\Packaginghandlingcost\Model\Total\Quote;

/**
 * Class Custom
 * @package Magenest\Discount\Model\Total\Quote
 */
class Custom extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return $this|\Magento\Quote\Model\Quote\Address\Total\AbstractTotal
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
		
		$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
		$session =  $objmanager->get("Magento\Checkout\Model\Session");
		$quote =$session->getQuote();
		$items =  $quote->getItemsCount();
		
		if($items == 1){
			$discount = 45;//0.1 * $quote->getGrandTotal();
			$total->addTotalAmount($this->getCode(), + $discount);
			$total->addBaseTotalAmount($this->getCode(), + $discount);
			$quote->setDiscount($discount);
		}
		elseif($items == 2){
			$discount = 55;//0.1 * $quote->getGrandTotal();
			$total->addTotalAmount($this->getCode(), + $discount);
			$total->addBaseTotalAmount($this->getCode(), + $discount);
			$quote->setDiscount($discount);
		}
		else
		{
			$discount = 65;//0.1 * $quote->getGrandTotal();
			$total->addTotalAmount($this->getCode(), + $discount);
			$total->addBaseTotalAmount($this->getCode(), + $discount);
			$quote->setDiscount($discount);
		}
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return array
     */
    /*public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $discount = 0.1 * $quote->getGrandTotal();

        return [
            'code'  => 'discount',
            'title' => $this->getLabel(),
            'value' => -$discount  //You can change the reduced amount, or replace it with your own variable
        ];
    }*/
}
