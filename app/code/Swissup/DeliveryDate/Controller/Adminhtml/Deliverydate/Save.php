<?php
namespace Swissup\DeliveryDate\Controller\Adminhtml\Deliverydate;

use Magento\Backend\App\Action;
use Magento\TestFramework\ErrorLog\Logger;

use Swissup\DeliveryDate\Model\DeliverydateFactory;
use Swissup\DeliveryDate\Helper\Data as DataHelper;

class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     *
     * @var \Swissup\DeliveryDate\Model\DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Swissup\DeliveryDate\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Swissup\DeliveryDate\Model\DeliverydateFactory $deliverydateFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Swissup\DeliveryDate\Model\DeliverydateFactory $deliverydateFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        DataHelper $dataHelper
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->deliverydateFactory = $deliverydateFactory;
        $this->dataHelper = $dataHelper;
        $this->orderFactory = $orderFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Swissup_DeliveryDate::save');
    }

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $time = $this->getRequest()->getParam('delivery-time');
        $date = $this->getRequest()->getParam('delivery-date');
        $date = $this->dataHelper->formatMySqlDateTime($date);

        $orderId = $this->getRequest()->getParam('order_id');
        $modelDeliveryDate = $this->deliverydateFactory
            ->create()
            ->loadByOrderId($orderId);

        $order = $this->orderFactory->create()->load($orderId);
        $quoteId = $order->getQuoteId();

        if (($date || $time) && $order->getId()) {
            $modelDeliveryDate
                ->setDate($date)
                ->setTimerange($time)
                ->setOrderId($orderId)
                ->setQuoteId($quoteId)
                ->save();
        }

        $data = $modelDeliveryDate->getData();

        $date = $modelDeliveryDate->getDate();
        $date = $this->dataHelper->getFormattedDate($date) . ' ' . $modelDeliveryDate->getTimerange();
        $data['formatted_date'] = $date;

        return $this->resultJsonFactory->create()->setData($data);
    }
}
