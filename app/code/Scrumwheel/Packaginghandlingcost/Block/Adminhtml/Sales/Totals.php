<?php

namespace Scrumwheel\Packaginghandlingcost\Block\Adminhtml\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $_dataHelper;
   

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magecomp\Extrafee\Helper\Data $dataHelper,
        \Magento\Directory\Model\Currency $currency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_dataHelper = $dataHelper;
        $this->_currency = $currency;
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    /**
     * @return string
     */
    public function getCurrencySymbol()
    {
        return $this->_currency->getCurrencySymbol();
    }

    /**
     *
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getOrder();
        $this->getSource();
		
		
		$orderId = $this->getRequest()->getParam('order_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($orderId);
		$items = $order->gettotal_item_count();
			if($items == 1)
				{
					$cost = 45;
				}
			elseif($items == 2)
				{
					$cost = 55;
				}
			else
				{
					$cost = 65;
				}




        if(!$this->getSource()->getFee()) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'value' => $cost,
                'label' => 'Packaging Handling Cost',
            ]
        );
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
		
        return $this;
    }
}
