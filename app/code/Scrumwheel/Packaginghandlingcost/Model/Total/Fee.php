<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Scrumwheel\Packaginghandlingcost\Model\Total;


class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null; 
    
    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator)
    {
        $this->quoteValidator = $quoteValidator;
    }
  public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
		
		
		$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
		$items =  $quote->getItemsCount();
			if($items == 1){
				$fee = 45;//0.1 * $quote->getGrandTotal();
				$balance = $fee;
				$total->addTotalAmount($this->getCode(), + $balance);
				$total->addBaseTotalAmount($this->getCode(), + $balance);

                $total->setFee($fee);
                $total->setBaseFee($fee);


				$quote->setGrandTotal($balance);
			}
			elseif($items == 2){
				$fee = 55;//0.1 * $quote->getGrandTotal();
				$balance = $fee;
				$total->addTotalAmount($this->getCode(), + $balance);
				$total->addBaseTotalAmount($this->getCode(), + $balance);

                $total->setFee($fee);
                $total->setBaseFee($fee);

				$quote->setGrandTotal($balance);
			}
			else
			{
				$fee = 65;//0.1 * $quote->getGrandTotal();
				$balance = $fee;
				$total->addTotalAmount($this->getCode(), + $balance);
				$total->addBaseTotalAmount($this->getCode(), + $balance);

                $total->setFee($fee);
                $total->setBaseFee($fee);

				$quote->setGrandTotal($balance);
			}				

        return $this;
    } 
    
    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
		$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
		$items =  $quote->getItemsCount();
			if($items == 1){
				return [
					'code' => 'fee',
					'title' => 'Fee',
					'value' => 45
				];
			}
			elseif($items == 2){
				return [
					'code' => 'fee',
					'title' => 'Fee',
					'value' => 55
				];
			}
			else{
				return [
					'code' => 'fee',
					'title' => 'Fee',
					'value' => 65
				];
			}
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Fee');
    }
}
