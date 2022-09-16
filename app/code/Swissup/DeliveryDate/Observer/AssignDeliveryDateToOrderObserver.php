<?php

namespace Swissup\DeliveryDate\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Swissup\DeliveryDate\Model\DeliverydateFactory;

class AssignDeliveryDateToOrderObserver implements ObserverInterface
{
    /**
     * @var DeliverydateFactory
     */
    protected $deliverydateFactory;

    /**
     * @param DeliverydateFactory $deliverydateFactory
     */
    public function __construct(DeliverydateFactory $deliverydateFactory)
    {
        $this->deliverydateFactory = $deliverydateFactory;
    }

    public function execute(EventObserver $observer)
    {
        /** @var  \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $modelDeliveryDate = $this->deliverydateFactory
            ->create()
            ->loadByQuoteId($order->getQuoteId());

        if ($modelDeliveryDate->getId()) {
            $modelDeliveryDate
                ->setOrderId($order->getId())
                ->save();
        }

        return $this;
    }
}
