<?php

namespace Swissup\DeliveryDate\Model\ResourceModel\Deliverydate;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Swissup\DeliveryDate\Model\Deliverydate::class,
            \Swissup\DeliveryDate\Model\ResourceModel\Deliverydate::class
        );
    }
}
