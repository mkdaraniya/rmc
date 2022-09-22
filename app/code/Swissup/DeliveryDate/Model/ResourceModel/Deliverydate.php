<?php
namespace Swissup\DeliveryDate\Model\ResourceModel;

/**
 * Deliverydate Deliverydate mysql resource
 */
class Deliverydate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_deliverydate', 'id');
    }

    /**
     * Load  by quoteId
     *
     * @param \Swissup\DeliveryDate\Model\Deliverdate $deliveryDate
     * @param int  $quoteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByQuoteId(\Swissup\DeliveryDate\Model\Deliverydate $deliveryDate, $quoteId)
    {
        $connection = $this->getConnection();
        $bind = ['quote_id' => $quoteId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->order(
            $this->getIdFieldName() . ' DESC'
        )->where(
            'quote_id = :quote_id'
        );

        // if ($customer->getSharingConfig()->isWebsiteScope()) {
        //     if (!$customer->hasData('website_id')) {
        //         throw new \Magento\Framework\Exception\LocalizedException(
        //             __('A customer website ID must be specified when using the website scope.')
        //         );
        //     }
        //     $bind['website_id'] = (int)$customer->getWebsiteId();
        //     $select->where('website_id = :website_id');
        // }

        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($deliveryDate, $id);
        } else {
            $deliveryDate->setData($bind);
        }

        return $this;
    }

    /**
     * Load  by orderId
     *
     * @param \Swissup\DeliveryDate\Model\Deliverdate $deliveryDate
     * @param int  $orderId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByOrderId(\Swissup\DeliveryDate\Model\Deliverydate $deliveryDate, $orderId)
    {
        $connection = $this->getConnection();
        $bind = ['order_id' => $orderId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->order(
            $this->getIdFieldName() . ' DESC'
        )->where(
            'order_id = :order_id'
        );

        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($deliveryDate, $id);
        } else {
            $deliveryDate->setData($bind);
        }

        return $this;
    }

    /**
     * Load  by orderId and Quote id
     *
     * @param \Swissup\DeliveryDate\Model\Deliverdate $deliveryDate
     * @param int  $orderId
     * @param int  $quoteId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByOrderIdAndQuoteId(\Swissup\DeliveryDate\Model\Deliverydate $deliveryDate, $orderId, $quoteId)
    {
        $connection = $this->getConnection();
        $bind = ['order_id' => $orderId, 'quote_id' => $quoteId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->order(
            $this->getIdFieldName() . ' DESC'
        )->where(
            'order_id = :order_id AND quote_id = :quote_id'
        );

        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($deliveryDate, $id);
        } else {
            $deliveryDate->setData($bind);
        }

        return $this;
    }
}
