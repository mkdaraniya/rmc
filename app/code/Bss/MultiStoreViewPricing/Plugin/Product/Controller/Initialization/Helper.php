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

namespace Bss\MultiStoreViewPricing\Plugin\Product\Controller\Initialization;

class Helper
{
    /**
     * @var \Bss\MultiStoreViewPricing\Helper\Data
     */
    private $helper;

    /**
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Merge product and default options for product
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param result $productOptions product options
     * @param array $productOptions product options
     * @param array $overwriteOptions default value options
     * @return array
     */
    public function afterMergeProductOptions(\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject, $result, $productOptions, $overwriteOptions)
    {
        if ($this->helper->isScopePrice()) {
            if (!is_array($productOptions)) {
                return [];
            }

            if (!is_array($overwriteOptions)) {
                return $productOptions;
            }

            foreach ($productOptions as $optionIndex => $option) {
                $optionId = $option['option_id'];
                $option = $this->overwriteValue($optionId, $option, $overwriteOptions);

                if (isset($option['values']) && isset($overwriteOptions[$optionId]['values'])) {
                    foreach ($option['values'] as $valueIndex => $value) {
                        if (isset($value['option_type_id'])) {
                            $valueId = $value['option_type_id'];
                            $value = $this->overwriteValue($valueId, $value, $overwriteOptions[$optionId]['values']);
                            $option['values'][$valueIndex] = $value;
                        }
                    }
                }

                $productOptions[$optionIndex] = $option;
            }
        }


        return $productOptions;
    }

    /**
     * Overwrite values of fields to default, if there are option id and field name in array overwriteOptions
     *
     * @param int $optionId
     * @param array $option
     * @param array $overwriteOptions
     * @return array
     */
    private function overwriteValue($optionId, $option, $overwriteOptions)
    {
        if (isset($overwriteOptions[$optionId])) {
            foreach ($overwriteOptions[$optionId] as $fieldName => $overwrite) {
                if ($overwrite && isset($option[$fieldName]) && isset($option['default_' . $fieldName])) {
                    $option[$fieldName] = $option['default_' . $fieldName];
                    if ('title' == $fieldName) {
                        $option['is_delete_store_title'] = 1;
                    }
                    if ('price' == $fieldName) {
                        $option['is_delete_store_price'] = 1;
                    }
                }
            }
        }
        return $option;
    }
}
