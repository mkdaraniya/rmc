<?php

namespace Magecomp\Extrafee\Block\Sales\Totals;


class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $_dataHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
         \Magecomp\Extrafee\Helper\Data $dataHelper,
        array $data = []
    )
    {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Check if we nedd display full tax total info
     *
     * @return bool
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }

    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
       // $store = $this->getStore();
		
		/*start my code*/
		$orderId = $this->getRequest()->getParam('order_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($orderId);
		$items = $order->gettotal_item_count();
			if($items == 1)
				{
					$cost = $this->_dataHelper->getExtrafee();
				}
			elseif($items == 2)
				{
					$cost = $this->_dataHelper->getExtrafee_1();
				}
			else
				{
					$cost = $this->_dataHelper->getExtrafee_2();
				}
		/*end my code*/		
		
        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'strong' => false,
                //'value' => $this->_source->getFee(),
                'value' => $cost,
                'label' => $this->_dataHelper->getFeeLabel(),
            ]
        );

        $parent->addTotal($fee, 'fee');

        return $this;
    }

}
