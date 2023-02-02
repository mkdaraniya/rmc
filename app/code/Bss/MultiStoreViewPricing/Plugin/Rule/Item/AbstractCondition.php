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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricing\Plugin\Rule\Item;

class AbstractCondition
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $pricecurrency;

    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    protected $helper;

    /**
     * AbstractCondition constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $pricecurrency
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ReinitableConfigInterface $config,
        \Magento\Directory\Model\Currency $currency,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->config = $config;
        $this->currency = $currency;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Rule\Model\Condition\AbstractCondition $subject
     * @param callable $proceed
     * @param object|array|int|string|float|bool $validatedValue product attribute value
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidateAttribute(\Magento\Rule\Model\Condition\AbstractCondition $subject, callable $proceed, $validatedValue)
    {
        if (is_object($validatedValue)) {
            return false;
        }

        /**
         * Condition attribute value
         */
        $value = $subject->getValueParsed();

        if (($subject->getAttribute() == 'base_subtotal' || $subject->getAttribute() == 'quote_item_price') && $this->helper->isScopePrice()) {
            $currency_store_code = $this->storeManager->getStore()->getBaseCurrency()->getCode();
            $currency_website_code = $this->storeManager->getWebsite()->getBaseCurrency()->getCode();
            $rateWebsiteToStore = (float)$this->currency->load($currency_website_code)->getAnyRate($currency_store_code);
            $value = round($value*$rateWebsiteToStore);
        }

        /**
         * Comparison operator
         */
        $option = $subject->getOperatorForValidate();

        // if operator requires array and it is not, or on opposite, return false
        if ($subject->isArrayOperatorType() xor is_array($value)) {
            return false;
        }

        $result = false;

        switch ($option) {
            case '==':
            case '!=':
                if (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = !empty(array_intersect($value, $validatedValue));
                } else {
                    if (is_array($validatedValue)) {
                        $result = count($validatedValue) == 1 && array_shift($validatedValue) == $value;
                    } else {
                        $result = $this->_compareValues($validatedValue, $value);
                    }
                }
                break;

            case '<=':
            case '>':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue <= $value;
                break;

            case '>=':
            case '<':
                if (!is_scalar($validatedValue)) {
                    return false;
                }
                $result = $validatedValue >= $value;
                break;

            case '{}':
            case '!{}':
                if (is_scalar($validatedValue) && is_array($value)) {
                    foreach ($value as $item) {
                        if (stripos($validatedValue, (string)$item) !== false) {
                            $result = true;
                            break;
                        }
                    }
                } elseif (is_array($value)) {
                    if (!is_array($validatedValue)) {
                        return false;
                    }
                    $result = array_intersect($value, $validatedValue);
                    $result = !empty($result);
                } else {
                    if (is_array($validatedValue)) {
                        $result = in_array($value, $validatedValue);
                    } else {
                        $result = $this->_compareValues($value, $validatedValue, false);
                    }
                }
                break;

            case '()':
            case '!()':
                if (is_array($validatedValue)) {
                    $result = count(array_intersect($validatedValue, (array)$value)) > 0;
                } else {
                    $value = (array)$value;
                    foreach ($value as $item) {
                        if ($this->_compareValues($validatedValue, $item)) {
                            $result = true;
                            break;
                        }
                    }
                }
                break;
        }

        if ('!=' == $option || '>' == $option || '<' == $option || '!{}' == $option || '!()' == $option) {
            $result = !$result;
        }

        return $result;
    }


    /**
     * Case and type insensitive comparison of values
     *
     * @param string|int|float $validatedValue
     * @param string|int|float $value
     * @param bool $strict
     * @return bool
     */
    protected function _compareValues($validatedValue, $value, $strict = true)
    {
        if ($strict && is_numeric($validatedValue) && is_numeric($value)) {
            return $validatedValue == $value;
        }

        $validatePattern = $validatedValue !== null ? preg_quote($validatedValue, '~') : '';
        if ($strict) {
            $validatePattern = '^' . $validatePattern . '$';
        }
        return (bool)preg_match('~' . $validatePattern . '~iu', $value);
    }
}
