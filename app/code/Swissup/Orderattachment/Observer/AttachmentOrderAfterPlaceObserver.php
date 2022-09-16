<?php
namespace Swissup\Orderattachment\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AttachmentOrderAfterPlaceObserver implements ObserverInterface
{
    protected $attachmentCollection;

    public function __construct(
        \Swissup\Orderattachment\Model\ResourceModel\Attachment\Collection $attachmentCollection,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->attachmentCollection = $attachmentCollection;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order) {
            return $this;
        }

        $attachments = $this->attachmentCollection
            ->addFieldToFilter('main_table.quote_id', $order->getQuoteId())
            ->addFieldToFilter('main_table.order_id', ['is' => new \Zend_Db_Expr('null')]);

        foreach ($attachments as $attachment) {
            try {
                $attachment->setOrderId($order->getId())->save();
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this;
    }
}
