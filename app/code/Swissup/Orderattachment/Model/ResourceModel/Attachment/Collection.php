<?php

namespace Swissup\Orderattachment\Model\ResourceModel\Attachment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'attachment_id';

    protected function _construct()
    {
        $this->_init('Swissup\Orderattachment\Model\Attachment', 'Swissup\Orderattachment\Model\ResourceModel\Attachment');
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addOrderIncrementId();

        return $this;
    }

    public function addOrderIncrementId()
    {
        $this->getSelect()->joinLeft(
            ['order' => $this->getTable('sales_order')],
            'main_table.order_id = order.entity_id',
            ['increment_id']
        );

        return $this;
    }
}
