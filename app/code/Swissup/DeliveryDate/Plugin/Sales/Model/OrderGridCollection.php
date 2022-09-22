<?php

namespace Swissup\DeliveryDate\Plugin\Sales\Model;

use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class OrderGridCollection
{
    /**
     * @var boolean
     */
    private $_mapUpdated = false;

    /**
     * @param  Collection $subject
     * @param  string|array $field
     * @param  null|string|array $condition
     */
    public function beforeAddFieldToFilter(Collection $subject, $field, $condition = null)
    {
        if (!$this->_mapUpdated) {
            $tableName = $subject->getResource()->getTable('swissup_deliverydate');
            $subject->addFilterToMap('delivery_date', $tableName . '.date');
            $this->_mapUpdated = true;
        }
    }

    /**
     * @param Collection $subject
     * @return null
     * @throws LocalizedException
     */
    public function beforeLoad(Collection $subject)
    {
        if ($subject->isLoaded()) {
            return;
        }

        $primaryKey = $subject->getResource()->getIdFieldName();
        $tableName = $subject->getResource()->getTable('swissup_deliverydate');

        $subject->getSelect()->joinLeft(
            $tableName,
            $tableName . '.order_id = main_table.' . $primaryKey,
            [
                'delivery_date' => $tableName . '.date',
                'delivery_time' => $tableName . '.timerange'
            ]
        );
    }

    public function afterGetSelectCountSql(Collection $subject, Select $select): Select
    {
        return $select->resetJoinLeft();
    }
}
