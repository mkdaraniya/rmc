<?php
/**
 * Magetop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magetop.com license that is
 * available through the world-wide-web at this URL:
 * https://www.magetop.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magetop
 * @package     Magetop_DeliveryTime
 * @copyright   Copyright (c) Magetop (https://www.magetop.com/)
 * @license     https://www.magetop.com/LICENSE.txt
 */

namespace Magetop\DeliveryTime\Helper;

use Magetop\DeliveryTime\Helper\AbstractData;
use Magetop\DeliveryTime\Model\System\Config\Source\DeliveryTime;
use Zend_Serializer_Exception;

/**
 * Class Data
 * @package Magetop\DeliveryTime\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'mpdeliverytime';

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isDisabled($store = null)
    {
        return !$this->isEnabled($store);
    }

    /**
     * Delivery Time
     *
     * @param null $store
     *
     * @return bool
     */
    public function isEnabledDeliveryTime($store = null)
    {
        return (bool) $this->getConfigGeneral('is_enabled_delivery_time', $store);
    }

    /**
     * House Security Code
     *
     * @param null $store
     *
     * @return bool
     */
    public function isEnabledHouseSecurityCode($store = null)
    {
        return (bool) $this->getConfigGeneral('is_enabled_house_security_code', $store);
    }

    /**
     * Delivery Comment
     *
     * @param null $store
     *
     * @return bool
     */
    public function isEnabledDeliveryComment($store = null)
    {
        return (bool) $this->getConfigGeneral('is_enabled_delivery_comment', $store);
    }

    /**
     * Date Format
     *
     * @param null $store
     *
     * @return string
     */
    public function getDateFormat($store = null)
    {
        return $this->getConfigGeneral('date_format', $store) ?: DeliveryTime::DAY_MONTH_YEAR_SLASH;
    }

    /**
     * Days Off
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getDaysOff($store = null)
    {
        return $this->getConfigGeneral('days_off', $store);
    }

    /**
     * Date Off
     *
     * @param null $store
     *
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public function getDateOff($store = null)
    {
        return $this->unserialize($this->getConfigGeneral('date_off', $store));
    }

    /**
     * Delivery Time
     *
     * @param null $store
     *
     * @return mixed
     * @throws Zend_Serializer_Exception
     */
    public function getDeliveryTIme($store = null)
    {
        return $this->unserialize($this->getConfigGeneral('delivery_time', $store));
    }
}
