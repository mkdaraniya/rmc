<?php
namespace Swissup\CheckoutFields\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

/**
 * CheckoutFields data mysql resource
 */
class Field extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_checkoutfields_field', 'field_id');
    }

    /**
     * Process field data before deleting
     *
     * @param AbstractModel $object
     * @return \Swissup\CheckoutFields\Model\ResourceModel\Field
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        $condition = ['field_id = ?' => (int)$object->getId()];
        $this->getConnection()->delete($this->getTable('swissup_checkoutfields_store'), $condition);
        return parent::_beforeDelete($object);
    }

    /**
     * Validate field data before save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $frontendLabel = $object->getFrontendLabel();
        if (is_array($frontendLabel)) {
            if (!isset($frontendLabel[0]) || $frontendLabel[0] === null || $frontendLabel[0] == '') {
                throw new \Magento\Framework\Exception\LocalizedException(__('The storefront label is not defined.'));
            }
            $object->setFrontendLabel($frontendLabel[0])->setStoreLabels($frontendLabel);
        }

        return parent::_beforeSave($object);
    }

    /**
     * Perform operations after object save
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        $table = $this->getTable('swissup_checkoutfields_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = ['field_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];
            $this->getConnection()->delete($table, $where);
        }
        if ($insert) {
            $data = [];
            foreach ($insert as $storeId) {
                $data[] = ['field_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
            }
            $this->getConnection()->insertMultiple($table, $data);
        }

        // save additional field data
        $this->saveStoreLabels(
            $object
        )->saveOption(
            $object
        );

        return parent::_afterSave($object);
    }

    /**
     * Perform operations after object load
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param \Swissup\CheckoutFields\Model\Field $object
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $stores = [(int)$object->getStoreId(), \Magento\Store\Model\Store::DEFAULT_STORE_ID];
            $select->join(
                ['fts' => $this->getTable('swissup_checkoutfields_store')],
                $this->getMainTable() . '.field_id = fts.field_id',
                ['store_id']
            )->where(
                'fts.store_id in (?)',
                $stores
            )->order(
                'store_id DESC'
            )->limit(
                1
            );
        }
        return $select;
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupStoreIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('swissup_checkoutfields_store'),
            'store_id'
        )->where(
            'field_id = :field_id'
        );
        $binds = [':field_id' => (int)$id];
        return $connection->fetchCol($select, $binds);
    }

    /**
     * Save store labels
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveStoreLabels(AbstractModel $object)
    {
        $storeLabels = $object->getStoreLabels();
        if (is_array($storeLabels)) {
            $connection = $this->getConnection();
            if ($object->getId()) {
                $condition = ['field_id =?' => $object->getId()];
                $connection->delete($this->getTable('swissup_checkoutfields_field_label'), $condition);
            }
            foreach ($storeLabels as $storeId => $label) {
                if ($storeId == 0 || !strlen($label)) {
                    continue;
                }
                $bind = ['field_id' => $object->getId(), 'store_id' => $storeId, 'value' => $label];
                $connection->insert($this->getTable('swissup_checkoutfields_field_label'), $bind);
            }
        }

        return $this;
    }

    /**
     * Save field options
     *
     * @param AbstractModel $object
     * @return $this
     */
    protected function saveOption(AbstractModel $object)
    {
        $option = $object->getOption();
        if (!is_array($option)) {
            return $this;
        }

        $defaultValue = $object->getDefault() ?: [];
        if (isset($option['value'])) {
            if (!is_array($object->getDefault())) {
                $object->setDefault([]);
            }
            $defaultValue = $this->processAttributeOptions($object, $option);
        }

        $this->saveDefaultValue($object, $defaultValue);
        return $this;
    }

    /**
     * Save changes of field options, return obtained default value
     *
     * @param AbstractModel $object
     * @param array $option
     * @return array
     */
    protected function processAttributeOptions($object, $option)
    {
        $defaultValue = [];
        foreach ($option['value'] as $optionId => $values) {
            $intOptionId = $this->updateAttributeOption($object, $optionId, $option);
            if ($intOptionId === false) {
                continue;
            }
            $this->updateDefaultValue($object, $optionId, $intOptionId, $defaultValue);
            $this->checkDefaultOptionValue($values);
            $this->updateAttributeOptionValues($intOptionId, $values);
        }
        return $defaultValue;
    }

    /**
     * Save option records
     *
     * @param AbstractModel $object
     * @param int $optionId
     * @param array $option
     * @return int|bool
     */
    protected function updateAttributeOption($object, $optionId, $option)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('swissup_checkoutfields_field_option');
        $intOptionId = (int)$optionId;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $connection->delete($table, ['option_id = ?' => $intOptionId]);
            }
            return false;
        }

        $sortOrder = empty($option['order'][$optionId]) ? 0 : $option['order'][$optionId];
        if (!$intOptionId) {
            $data = ['field_id' => $object->getId(), 'sort_order' => $sortOrder];
            $connection->insert($table, $data);
            $intOptionId = $connection->lastInsertId($table);
        } else {
            $data = ['sort_order' => $sortOrder];
            $where = ['option_id = ?' => $intOptionId];
            $connection->update($table, $data, $where);
        }

        return $intOptionId;
    }

    /**
     * Check default option value presence
     *
     * @param array $values
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkDefaultOptionValue($values)
    {
        if (!isset($values[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Default option value is not defined'));
        }
    }

    /**
     * Update field default value
     *
     * @param AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     * @return void
     */
    protected function updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            if ($frontendInput === 'multiselect') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'select') {
                $defaultValue = [$intOptionId];
            }
        }
    }

    /**
     * Save option values records per store
     *
     * @param int $optionId
     * @param array $values
     * @return void
     */
    protected function updateAttributeOptionValues($optionId, $values)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('swissup_checkoutfields_field_option_value');

        $connection->delete($table, ['option_id = ?' => $optionId]);

        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!empty($values[$storeId]) || isset($values[$storeId]) && $values[$storeId] == '0') {
                $data = ['option_id' => $optionId, 'store_id' => $storeId, 'value' => $values[$storeId]];
                $connection->insert($table, $data);
            }
        }
    }

    /**
     * Save field default value
     *
     * @param AbstractModel $object
     * @param array $defaultValue
     * @return void
     */
    protected function saveDefaultValue($object, $defaultValue)
    {
        if ($defaultValue !== null) {
            $bind = ['default_value' => implode(',', $defaultValue)];
            $where = ['field_id = ?' => $object->getId()];
            $this->getConnection()->update($this->getMainTable(), $bind, $where);
        }
    }

    /**
     * Retrieve store labels by given field id
     *
     * @param int $fieldId
     * @return array
     */
    public function getStoreLabelsByFieldId($fieldId)
    {
        $connection = $this->getConnection();
        $bind = [':field_id' => $fieldId];
        $select = $connection->select()->from(
            $this->getTable('swissup_checkoutfields_field_label'),
            ['store_id', 'value']
        )->where(
            'field_id = :field_id'
        );

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Load field by code
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $code
     * @return bool
     */
    public function loadByCode(AbstractModel $object, $code)
    {
        $select = $this->_getLoadSelect('attribute_code', $code, $object);
        $data = $this->getConnection()->fetchRow($select);

        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
            return true;
        }
        return false;
    }
}
