<?php
namespace Magecomp\Extrafee\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

use Magecomp\Extrafee\Helper\Data;

class AddFeeToOrderObserver implements ObserverInterface
{
    /**
     * Set payment fee to order
     *
     * @param EventObserver $observer
     * @return $this
     */
	protected $helper; 
	 public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $ExtrafeeFee = $quote->getFee();
		
		$objmanager = \Magento\Framework\App\ObjectManager::getInstance();
		$items =  $quote->getItemsCount();
		if($items == 1){
		$label = $this->helper->getExtrafee();
		}
		elseif($items == 2){
		$label = $this->helper->getExtrafee_1();	
		}
		else{
		$label = $this->helper->getExtrafee_2();	
		}
		
		
		
        if (!$ExtrafeeFee) {
            return $this;
        }
        //Set fee data to order
        $order = $observer->getOrder();
        $order->setData('fee', $ExtrafeeFee);
		
		$order = $observer->getOrder();	
		$order->setData('pacakging_handaling_cost', $label);
        
		return $this;
    }
}
