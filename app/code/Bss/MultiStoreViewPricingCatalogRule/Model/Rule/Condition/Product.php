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
namespace Bss\MultiStoreViewPricingCatalogRule\Model\Rule\Condition;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Framework\App\ObjectManager;

/**
 * @method string getAttribute() Returns attribute code
 */
class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{   
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storemanager;

    /**
     * @var \Bss\MultiStoreViewPricingCatalogRule\Model\ResourceModel\Currency
     */
    protected $bscurrencyfactory;

    /**
     * @var ProductCategoryList
     */
    private $productCategoryList;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $bshelper;

    /**
     * @param \Magento\Rule\Model\Condition\Context $context
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Store\Model\StoreManagerInterface $storemanager
     * @param \Bss\MultiStoreViewPricingCatalogRule\Model\ResourceModel\Currency $bscurrency
     * @param \Bss\MultiStoreViewPricing\Helper\Data $bshelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product $productResource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param ProductCategoryList|null $categoryList
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Rule\Model\Condition\Context $context,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Bss\MultiStoreViewPricingCatalogRule\Model\ResourceModel\Currency $bscurrency,
        \Bss\MultiStoreViewPricing\Helper\Data $bshelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ResourceModel\Product $productResource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attrSetCollection,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        array $data = [],
        ProductCategoryList $categoryList = null
    ) {

        $this->request = $request;
        $this->storemanager = $storemanager;
        $this->bscurrency = $bscurrency;
        $this->bshelper = $bshelper;
        parent::__construct(
            $context,
            $backendData,
            $config,
            $productFactory,
            $productRepository,
            $productResource,
            $attrSetCollection,
            $localeFormat,
            $data
        );
    }

    /**
     * Validate product attribute value for condition
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        if (!$this->bshelper->isScopePrice()) {
            return parent::validate($model);
        }

        $attrCode = $this->getAttribute();
        if ('category_ids' == $attrCode) {
            return parent::validate($model);
        }

        $oldAttrValue = $model->getData($attrCode);
        if ($oldAttrValue === null) {
            return false;
        }

        $this->_setAttributeValue($model);
        $this->_preparePriceValue($model);

        $result = $this->validateAttribute($model->getData($attrCode));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    /**
     * Prepare multiselect attribute value
     *
     * @param mixed $value
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Model\AbstractModel $model
     * @return mixed
     */
    protected function _preparePriceValue(\Magento\Framework\Model\AbstractModel $model)
    {
        $attribute = $model->getResource()->getAttribute($this->getAttribute());
        $value = $model->getData($this->getAttribute());
        // convert price
        if ($attribute && $this->getAttribute() == 'price') {
            $store = $this->storemanager->getStore($model->getStoreId());
            $websiteid = $store->getWebsiteId();
            $currency_from = $store->getBaseCurrency()->getCode();
            $websiteCurrency = $this->storemanager->getWebsite($websiteid)->getBaseCurrency()->getCode();
            $rates = $this->bscurrency->getCurrencyRates($websiteCurrency);
            $currency_to = $websiteCurrency;
            if ($model->getSingleRule()) {
                $products = $model->getCollection();
                $products->addAttributeToSelect('price');
                $products->addFieldToFilter('entity_id', $model->getEntityId());
                $products->setStore($model->getStoreId());
                foreach ($products as $product) {
                    $value = $product->getPrice();
                    break;
                }
            } else {
                $minrate = min(array_column($rates, 'rate'));
                foreach ($rates as $rate) {
                    if ($rate['rate'] == $minrate) {
                        $currency_to = $rate['currency_to'];
                        break;
                    }
                }
            }
            $currency_rate = (float)$this->bscurrency->getCurrencyRate($currency_from, $currency_to);
            $value = $value*$currency_rate;
            $model->setData($this->getAttribute(), $value);
        }
        // end
        return $this;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function loadArray($arr)
    {
        if (!$this->bshelper->isScopePrice()) {
            return parent::loadArray($arr);
        }
        
        $this->setAttribute(isset($arr['attribute']) ? $arr['attribute'] : false);
        $attribute = $this->getAttributeObject();

        $isContainsOperator = !empty($arr['operator']) && in_array($arr['operator'], ['{}', '!{}']);
        if ($attribute && $attribute->getBackendType() == 'decimal' && !$isContainsOperator) {
            if (isset($arr['value'])) {
                if (!empty($arr['operator']) && in_array(
                    $arr['operator'],
                    ['!()', '()']
                ) && false !== strpos(
                    $arr['value'],
                    ','
                )
                ) {
                    $tmp = [];
                    foreach (explode(',', $arr['value']) as $value) {
                        $tmp[] = $this->_localeFormat->getNumber($value);
                    }
                    $arr['value'] = implode(',', $tmp);
                } else {
                    $arr['value'] = $this->_localeFormat->getNumber($arr['value']);
                }
            } else {
                $arr['value'] = false;
            }
            $arr['is_value_parsed'] = isset(
                $arr['is_value_parsed']
            ) ? $this->_localeFormat->getNumber(
                $arr['is_value_parsed']
            ) : false;
        }

        // convert value 
        $actionName = $this->request->getActionName();
        if (!$actionName && $this->getAttribute() == 'price') {
            foreach ($arr['website_ids'] as $websiteid) {
                $websiteCurrency = $this->storemanager->getWebsite($websiteid)->getBaseCurrency()->getCode();
                $rates = $this->bscurrency->getCurrencyRates($websiteCurrency);
                $arr['value'] = $arr['value']*min(array_column($rates, 'rate'));
            }
        }
        // end

        return parent::loadArray($arr);
    }
}
