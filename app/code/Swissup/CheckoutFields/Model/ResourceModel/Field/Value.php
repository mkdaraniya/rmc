<?php
namespace Swissup\CheckoutFields\Model\ResourceModel\Field;

class Value extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('swissup_checkoutfields_values', 'value_id');
    }

    /**
     * Load by quoteId, fieldId and storeId
     *
     * @param \Swissup\CheckoutFields\Model\Field\Value $fieldValue
     * @param int $quoteId
     * @param int $fieldId
     * @param int $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByQuoteFieldAndStore(
        \Swissup\CheckoutFields\Model\Field\Value $fieldValue,
        $quoteId,
        $fieldId,
        $storeId
    ) {
        $connection = $this->getConnection();
        $bind = [
            'quote_id' => $quoteId,
            'field_id' => $fieldId,
            'store_id' => $storeId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'quote_id = :quote_id AND field_id = :field_id AND store_id = :store_id'
        );
        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($fieldValue, $id);
        } else {
            $fieldValue->setData($bind);
        }

        return $this;
    }

    /**
     * Load by orderId, fieldId and storeId
     *
     * @param \Swissup\CheckoutFields\Model\Field\Value $fieldValue
     * @param int $orderId
     * @param int $fieldId
     * @param int $storeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByOrderFieldAndStore(
        \Swissup\CheckoutFields\Model\Field\Value $fieldValue,
        $orderId,
        $fieldId,
        $storeId
    ) {
        $connection = $this->getConnection();
        $bind = [
            'order_id' => $orderId,
            'field_id' => $fieldId,
            'store_id' => $storeId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'order_id = :order_id AND field_id = :field_id AND store_id = :store_id'
        );
        $id = $connection->fetchOne($select, $bind);
        if ($id) {
            $this->load($fieldValue, $id);
        } else {
            $fieldValue->setData($bind);
        }

        return $this;
    }
}
