<?php

namespace Swissup\DeliveryDate\Model;

class DeliverydateRepository
{
    /**
     * @var \Swissup\DeliveryDate\Model\ResourceModel\Deliverydate\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Swissup\DeliveryDate\Model\ResourceModel\Deliverydate\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Swissup\DeliveryDate\Model\ResourceModel\Deliverydate\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param mixed $id
     * @return mixed
     */
    public function getByOrderId($id)
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter('order_id', $id);

        if (is_array($id)) {
            return $collection;
        }

        return $collection->getFirstItem();
    }
}
