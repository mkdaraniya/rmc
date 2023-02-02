<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This package designed for Magento COMMUNITY edition
 * BSS Commerce does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * BSS Commerce does not provide extension support in case of
 * incorrect edition usage.
 * =================================================================
 *
 * @category  BSS
 * @package   Bss_MultiStoreViewPricingCatalogRule
 * @author    Extension Team
 * @copyright Copyright (c) 2016-2017 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Model\ResourceModel;

class Currency extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main and currency rate tables
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_currency', 'currency_code');
    }

    /**
     * Return currency rate
     *
     * @param string|array $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRate($currency, $toCurrencies)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
                $this->getTable('directory_currency_rate'),
                ['rate']
            )->where(
                'currency_from = ?', $currency
            )->where(
                'currency_to = ?', $toCurrencies
            );
        $rate = $connection->fetchOne($select);
        return $rate;
    }

    /**
     * Return currency rates
     *
     * @param string|array $currency
     * @param array $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
                $this->getTable('directory_currency_rate'),
                ['currency_to', 'rate']
            )->where(
                'currency_from = ?', $currency
            );
        $rates = $connection->fetchAll($select);
        return $rates;
    }
}
