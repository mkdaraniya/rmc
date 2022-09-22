<?php

namespace Swissup\CheckoutFields\Plugin\Model;

use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class OrderGridCollection
{
    const TABLE_PREFIX = 'swissupcfv_';

    /**
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\Collection
     */
    private $fields;

    /**
     * @var array
     */
    private $map = [];

    /**
     * @var \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory
     */
    private $fieldsFactory;

    /**
     * @var \Swissup\CheckoutFields\Helper\Data
     */
    private $helper;

    /**
     * @param \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsFactory
     * @param \Swissup\CheckoutFields\Helper\Data $helper
     */
    public function __construct(
        \Swissup\CheckoutFields\Model\ResourceModel\Field\CollectionFactory $fieldsFactory,
        \Swissup\CheckoutFields\Helper\Data $helper
    ) {
        $this->fieldsFactory = $fieldsFactory;
        $this->helper = $helper;
    }

    /**
     * @param Collection $subject
     * @param string|array $field
     * @param null|string|array $condition
     */
    public function beforeAddFieldToFilter(Collection $subject, $fieldName, $condition = null)
    {
        if (!is_string($fieldName) || !empty($this->map[$fieldName])) {
            return;
        }

        $fields = $this->getFields();

        if (!count($fields) || !in_array($fieldName, $fields->getColumnValues('attribute_code'))) {
            return;
        }

        $field = $fields->getItemByColumnValue('attribute_code', $fieldName);
        $alias = $this->getTableAlias($fieldName) . '.value';

        if ($field && $field->getFrontendInput() === 'date') {
            $alias = new \Zend_Db_Expr("str_to_date({$alias}, '%m/%d/%Y')");
        }

        $subject->addFilterToMap($fieldName, $alias);

        $this->map[$fieldName] = true;
    }

    public function beforeLoad(Collection $subject)
    {
        if ($subject->isLoaded() || !$this->helper->isEnabled()) {
            return;
        }

        $fields = $this->getFields();
        if (!count($fields)) {
            return;
        }

        $select = $subject->getSelect();
        foreach ($fields as $field) {
            $id = (int) $field->getFieldId();
            $code = $field->getAttributeCode();
            $valueTable = $this->getTableAlias($code);

            $select->joinLeft(
                [$valueTable => $subject->getTable('swissup_checkoutfields_values')],
                implode(' AND ', [
                    "{$valueTable}.field_id = {$id}",
                    "{$valueTable}.order_id = main_table.entity_id",
                ]),
                [$code => "{$valueTable}.value"]
            );
        }

        // Fix column in where clause is ambiguous error
        $where = $select->getPart('where');
        foreach ($where as &$item) {
            if (strpos($item, '(`store_id`') !== false) {
                $item = str_replace('`store_id`', '`main_table`.`store_id`', $item);
            }

            if (strpos($item, '(`created_at`') !== false) {
                $item = str_replace('`created_at`', '`main_table`.`created_at`', $item);
            }
        }
        $select->setPart('where', $where);
    }

    public function afterGetSelectCountSql(Collection $subject, Select $select): Select
    {
        $from = implode(' ', array_keys($select->getPart('from')));

        if (strpos($from, self::TABLE_PREFIX) === false) {
            return $select;
        }

        return $select->resetJoinLeft();
    }

    private function getTableAlias($attributeCode)
    {
        return self::TABLE_PREFIX . $attributeCode;
    }

    private function getFields()
    {
        if (!$this->fields) {
            $this->fields = $this->fieldsFactory->create()->addUsedInGridFilter(1);
        }

        return $this->fields;
    }
}
