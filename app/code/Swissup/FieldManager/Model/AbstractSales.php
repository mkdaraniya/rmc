<?php
namespace Swissup\FieldManager\Model;

abstract class AbstractSales extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::RESOURCE_MODEL_CLASS);
    }

    public function beforeSave()
    {
        if ($this->_dataSaveAllowed && !$this->getResource()->checkMainEntity($this)) {
            $this->_dataSaveAllowed = false;
        }

        return parent::beforeSave();
    }
}
