<?php
namespace Swissup\CheckoutFields\Model\ResourceModel\Field;

class Option extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('swissup_checkoutfields_field_option', 'option_id');
    }
}
