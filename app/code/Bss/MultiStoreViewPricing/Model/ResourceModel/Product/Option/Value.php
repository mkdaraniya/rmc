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
 * @category   BSS
 * @package    Bss_MultiStoreViewPricing
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\MultiStoreViewPricing\Model\ResourceModel\Product\Option;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class Value extends \Magento\Catalog\Model\ResourceModel\Product\Option\Value
{
    /**
     * @var FormatInterface
     */
    protected $localeFormat;

    /**
     * Save option value price data
     *
     * @param AbstractModel $object
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _saveValuePrices(AbstractModel $object)
    {
        $objectPrice = $object->getPrice();
        $priceTable = $this->getTable('catalog_product_option_type_price');
        $formattedPrice = $this->getLocaleFormatter()->getNumber($objectPrice);

        $price = (double)sprintf('%F', $formattedPrice);
        $priceType = $object->getPriceType();

         $scope = (int)$this->_config->getValue(
            Store::XML_PATH_PRICE_SCOPE,
            ScopeInterface::SCOPE_STORE
        );

        if ($scope == 2) {
            foreach ([Store::DEFAULT_STORE_ID, $object->getStoreId()] as $storeId) {
                $select = $this->getConnection()->select()->from(
                    $priceTable,
                    ['option_type_id']
                )->where(
                    'option_type_id = ?',
                    (int)$object->getId()
                )->where(
                    'store_id = ?',
                    (int)$storeId
                );
                $optionTypeId = $this->getConnection()->fetchOne($select);
                $existInCurrentStore = $this->getOptionIdFromOptionTable($priceTable, (int)$object->getId(), (int)$storeId);

                if ($storeId != Store::DEFAULT_STORE_ID && $object->getData('is_delete_store_price')) {
                    $object->unsetData('price');
                }

                /*** Checking whether title is not null ***/
                if ($object->getPrice()!= null) {
                    if ($existInCurrentStore) {
                        if ($storeId == $object->getStoreId()) {
                            $where = [
                                'option_type_id = ?' => (int)$optionTypeId,
                                'store_id = ?' => $storeId,
                            ];
                            $bind = ['price' => $price, 'price_type' => $priceType];
                            $this->getConnection()->update($priceTable, $bind, $where);
                        }
                    } else {
                        $existInDefaultStore = $this->getOptionIdFromOptionTable(
                            $priceTable,
                            (int)$object->getId(),
                            Store::DEFAULT_STORE_ID
                        );
                        // we should insert record into not default store only of if it does not exist in default store
                        if (($storeId == Store::DEFAULT_STORE_ID && !$existInDefaultStore)
                            || ($storeId != Store::DEFAULT_STORE_ID && !$existInCurrentStore)
                        ) {
                            $bind = [
                                'option_type_id' => (int)$object->getId(),
                                'store_id' => $storeId,
                                'price' => $price,
                                'price_type' => $priceType
                            ];
                            $this->getConnection()->insert($priceTable, $bind);
                        }
                    }
                } else {
                    if ($storeId
                        && $optionTypeId
                        && $object->getStoreId() > Store::DEFAULT_STORE_ID
                    ) {
                        $where = [
                            'option_type_id = ?' => (int)$optionTypeId,
                            'store_id = ?' => $storeId,
                        ];
                        $this->getConnection()->delete($priceTable, $where);
                    }
                }
            }
        } else {
            parent::_saveValuePrices($object);
        }
    }

    /**
     * Get FormatInterface to convert price from string to number format
     *
     * @return FormatInterface
     * @deprecated 101.0.8
     */
    private function getLocaleFormatter()
    {
        if ($this->localeFormat === null) {
            $this->localeFormat = ObjectManager::getInstance()
                ->get(FormatInterface::class);
        }
        return $this->localeFormat;
    }
}
