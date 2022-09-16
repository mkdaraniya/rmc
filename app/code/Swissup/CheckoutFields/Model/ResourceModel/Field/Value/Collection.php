<?php
namespace Swissup\CheckoutFields\Model\ResourceModel\Field\Value;

/**
 * CheckoutFields Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'value_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\CheckoutFields\Model\Field\Value', 'Swissup\CheckoutFields\Model\ResourceModel\Field\Value');
    }

    /**
     * Get SQL for get record count
     *
     * Extra GROUP BY strip added.
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelectCountSql()
    {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);
        return $countSelect;
    }

    /**
     * Add field info
     *
     * @param int $storeId
     * @return $this
     */
    protected function joinFieldInfo()
    {
        $connection = $this->getConnection();
        $this->getSelect()->joinLeft(
            ['fi' => $this->getTable('swissup_checkoutfields_field')],
            'fi.field_id = main_table.field_id',
            [
                'frontend_label' => 'fi.frontend_label',
                'frontend_input' => 'frontend_input',
                'attribute_code' => 'attribute_code'
            ]
        );

        return $this;
    }

    /**
     * Add store label to field by specified store id
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreLabel($storeId)
    {
        $this->joinFieldInfo();

        $connection = $this->getConnection();
        $joinExpression = $connection->quoteInto(
            'fl.field_id = main_table.field_id AND fl.store_id = ?',
            (int)$storeId
        );
        $this->getSelect()->joinLeft(
            ['fl' => $this->getTable('swissup_checkoutfields_field_label')],
            $joinExpression,
            ['store_label' => $connection->getIfNullSql('fl.value', 'fi.frontend_label')]
        );

        return $this;
    }

    /**
     * Filter collection by quote_id
     *
     * @return $this
     */
    public function addQuoteFilter($quote_id)
    {
        $this->getSelect()
            ->where('main_table.quote_id = ?', $quote_id);
        return $this;
    }

    /**
     * Filter collection by store_id
     *
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->getSelect()
            ->where('main_table.store_id = ?', $storeId);
        return $this;
    }

    /**
     * Filter collection by order_id
     *
     * @return $this
     */
    public function addOrderFilter($order_id)
    {
        $this->getSelect()
            ->where('main_table.order_id = ?', $order_id);
        return $this;
    }

    /**
     * Skip empty values
     *
     * @return $this
     */
    public function addEmptyValueFilter()
    {
        $this->getSelect()
            ->where('main_table.value IS NOT NULL');
        return $this;
    }
}
