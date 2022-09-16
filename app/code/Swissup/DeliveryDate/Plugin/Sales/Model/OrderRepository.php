<?php

namespace Swissup\DeliveryDate\Plugin\Sales\Model;

class OrderRepository
{
    /**
     * @var \Swissup\DeliveryDate\Helper\Data
     */
    private $helper;

    /**
     * @var \Swissup\DeliveryDate\Model\DeliverydateRepository
     */
    private $repository;

    /**
     * @param \Swissup\DeliveryDate\Helper\Data $helper
     * @param \Swissup\DeliveryDate\Model\DeliverydateRepository $repository
     */
    public function __construct(
        \Swissup\DeliveryDate\Helper\Data $helper,
        \Swissup\DeliveryDate\Model\DeliverydateRepository $repository
    ) {
        $this->helper = $helper;
        $this->repository = $repository;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $entity
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $entity
    ) {
        if (!$this->helper->isEnabled()) {
            return $entity;
        }

        $this->setDeliveryDate(
            $entity,
            $this->repository->getByOrderId($entity->getId())
        );

        return $entity;
    }

    /**
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult
    ) {
        if (!$this->helper->isEnabled()) {
            return $searchResult;
        }

        $ids = [];
        foreach ($searchResult->getItems() as $order) {
            $ids[] = $order->getId();
        }

        $entities = $this->repository->getByOrderId($ids);

        foreach ($searchResult->getItems() as $order) {
            $this->setDeliveryDate(
                $order,
                $entities->getItemByColumnValue('order_id', $order->getId())
            );
        }

        return $searchResult;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Swissup\DeliveryDate\Api\Data\DeliverydateInterface|null $deliverydate
     * @return void
     */
    private function setDeliveryDate(
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Swissup\DeliveryDate\Api\Data\DeliverydateInterface $deliverydate = null
    ) {
        if (!$deliverydate) {
            return;
        }

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes->setSwissupDeliveryDate($deliverydate);
        $order->setExtensionAttributes($extensionAttributes);
    }
}
