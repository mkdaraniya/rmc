<?php

namespace Swissup\DeliveryDate\Observer\Adminhtml;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

use Swissup\DeliveryDate\Model\DeliverydateFactory;
use Swissup\DeliveryDate\Helper\Data as DataHelper;

/**
 * Class SaveDeliveryDateToQuoteObserver
 * @package Swissup\DeliveryDate\Model\Observer\Adminhtml
 */
class SaveDeliveryDateToQuoteObserver implements ObserverInterface
{

    /**
     * @var DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     * @var \Swissup\DeliveryDate\Helper\Data
     */
    protected $dataHelper;

    /**
     *
     * @param DeliverydateFactory $deliverydateFactory
     * @param DataHelper $dataHelper
     */
    public function __construct(
        DeliverydateFactory $deliverydateFactory,
        DataHelper $dataHelper
    ) {
        $this->deliverydateFactory = $deliverydateFactory;
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $time = $observer->getRequestModel()->getParam('delivery-time');
        $date = $observer->getRequestModel()->getParam('delivery-date');
        $date = $this->dataHelper->formatMySqlDateTime($date);

        $session = $observer->getSession();
        $orderId = $session->getOrderId();
        $quoteId = $session->getQuoteId();

        $modelDeliveryDate = $this->deliverydateFactory
            ->create()
            ->loadByOrderIdAndQuoteId($orderId, $quoteId);

        if ($date || $time) {
            $modelDeliveryDate
                ->setDate($date)
                ->setTimerange($time)
                ->setOrderId($orderId)
                ->setQuoteId($quoteId)
                ->save();
        } elseif ($modelDeliveryDate->getId()) {
            $modelDeliveryDate->delete();
        }
    }
}
