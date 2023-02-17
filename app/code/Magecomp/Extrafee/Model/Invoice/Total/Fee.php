<?php

namespace Magecomp\Extrafee\Model\Invoice\Total;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    /**
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
		$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
		$items = $invoice->gettotal_item_count();
		if($items == 1)
		{
		$invoice->setFee(0);
        $amount = $invoice->getOrder()->getFee();
        $invoice->setFee($amount);
       

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getFee());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getFee());
		}
		elseif($items == 2)
		{
		$invoice->setFee(0);
        $amount = $invoice->getOrder()->getFee();
        $invoice->setFee($amount);
       

        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getExtrafee_1());
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getFee());
		}
		else
		{
		$invoice->setFee(0);
        $amount = $invoice->getOrder()->getFee();
        $invoice->setFee($amount);
       

        $invoice->setGrandTotal($invoice->getGrandTotal() + 65);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getFee());
		}
        return $this;
    }
}
