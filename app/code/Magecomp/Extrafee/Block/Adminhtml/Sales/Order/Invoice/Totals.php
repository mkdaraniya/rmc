<?php

namespace Magecomp\Extrafee\Block\Adminhtml\Sales\Order\Invoice;

class Totals extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magecomp\Extrafee\Helper\Data
     */
    protected $_dataHelper;

    /**
     * Order invoice
     *
     * @var \Magento\Sales\Model\Order\Invoice|null
     */
    protected $_invoice = null;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * OrderFee constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
            \Magecomp\Extrafee\Helper\Data $dataHelper,
        array $data = []
    ) {
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function getInvoice()
    {
        return $this->getParentBlock()->getInvoice();
    }
    /**
     * Initialize payment fee totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $this->getParentBlock();
        $this->getInvoice();
        $this->getSource();
		
		$orderId = $this->getRequest()->getParam('order_id');
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($orderId);
		$items = $order->gettotal_item_count();
		if($items == 1)
				{
					$cost = $order->getFee();
				}
			elseif($items == 2)
				{
					$cost = $order->getFee();
				}
			else
				{
					$cost = $order->getFee();
				}
				
        if(!$this->getSource()->getFee()) {
            return $this;
        }
        $total = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'value' => $cost,
                'label' => $this->_dataHelper->getFeeLabel(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
