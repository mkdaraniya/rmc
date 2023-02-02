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

namespace Bss\MultiStoreViewPricing\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\Config\Source\Product\Options\Price as ProductOptionsPrice;
use Magento\Framework\UrlInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\Container;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Element\Select;
use Magento\Ui\Component\Form\Element\DataType\Number;
use Magento\Framework\Locale\CurrencyInterface;

/**
 * Data provider for "Customizable Options" panel
 *
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 101.0.0
 */
class CustomOptions extends \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\CustomOptions
{
    /**
     * @param LocatorInterface $locator
     * @param StoreManagerInterface $storeManager
     * @param ConfigInterface $productOptionsConfig
     * @param ProductOptionsPrice $productOptionsPrice
     * @param UrlInterface $urlBuilder
     * @param ArrayManager $arrayManager
     * @param \Bss\MultiStoreViewPricing\Helper\Data $helper
     */
    public function __construct(
        LocatorInterface $locator,
        StoreManagerInterface $storeManager,
        ConfigInterface $productOptionsConfig,
        ProductOptionsPrice $productOptionsPrice,
        UrlInterface $urlBuilder,
        ArrayManager $arrayManager,
        \Bss\MultiStoreViewPricing\Helper\Data $helper
    ) {
        parent::__construct(
            $locator,
            $storeManager,
            $productOptionsConfig,
            $productOptionsPrice,
            $urlBuilder,
            $arrayManager
        );
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     * @since 101.0.0
     */
    public function modifyData(array $data)
    {
        $options = [];
        $productOptions = $this->locator->getProduct()->getOptions() ?: [];

        /** @var \Magento\Catalog\Model\Product\Option $option */
        foreach ($productOptions as $index => $option) {
            $optionData = $option->getData();
            $optionData[static::FIELD_IS_USE_DEFAULT] = !$option->getData(static::FIELD_STORE_TITLE_NAME);
            $optionData['is_use_default_price'] = !$option->getData('store_price');

            $options[$index] = $this->formatPriceByPath(static::FIELD_PRICE_NAME, $optionData);
            $values = $option->getValues() ?: [];

            foreach ($values as $value) {
                $value->setData(static::FIELD_IS_USE_DEFAULT, !$value->getData(static::FIELD_STORE_TITLE_NAME));
                $value->setData('is_use_default_price', !$value->getData('store_price'));
            }
            /** @var \Magento\Catalog\Model\Product\Option $value */
            foreach ($values as $value) {
                $options[$index][static::GRID_TYPE_SELECT_NAME][] = $this->formatPriceByPath(
                    static::FIELD_PRICE_NAME,
                    $value->getData()
                );
            }
        }

        return array_replace_recursive(
            $data,
            [
                $this->locator->getProduct()->getId() => [
                    static::DATA_SOURCE_DEFAULT => [
                        static::FIELD_ENABLE => 1,
                        static::GRID_OPTIONS_NAME => $options
                    ]
                ]
            ]
        );
    }

    /**
     * Get config for grid for "select" types
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getSelectTypeGridConfig($sortOrder)
    {
        $options = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'imports' => [
                            'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                            'optionTypeId' => '${ $.provider }:${ $.parentScope }.option_type_id',
                            'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default',
                            '__disableTmpl' => ['optionId' => false, 'optionTypeId' => false, 'isUseDefault' => false],
                        ],
                        'service' => [
                            'template' => 'Magento_Catalog/form/element/helper/custom-option-type-service',
                        ],
                    ],
                ],
            ],
        ];

        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'addButtonLabel' => __('Add Value'),
                        'componentType' => DynamicRows::NAME,
                        'component' => 'Magento_Ui/js/dynamic-rows/dynamic-rows',
                        'additionalClasses' => 'admin__field-wide',
                        'deleteProperty' => static::FIELD_IS_DELETE,
                        'deleteValue' => '1',
                        'renderDefaultRecord' => false,
                        'sortOrder' => $sortOrder,
                    ],
                ],
            ],
            'children' => [
                'record' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'componentType' => Container::NAME,
                                'component' => 'Magento_Ui/js/dynamic-rows/record',
                                'positionProvider' => static::FIELD_SORT_ORDER_NAME,
                                'isTemplate' => true,
                                'is_collection' => true,
                            ],
                        ],
                    ],
                    'children' => [
                        static::FIELD_TITLE_NAME => $this->getTitleFieldConfig(
                            10,
                            $this->locator->getProduct()->getStoreId() ? $options : []
                        ),
                        static::FIELD_PRICE_NAME => $this->getPriceFieldConfigForSelectType(20),
                        static::FIELD_PRICE_TYPE_NAME => $this->getPriceTypeFieldConfig(30, ['fit' => true]),
                        static::FIELD_SKU_NAME => $this->getSkuFieldConfig(40),
                        static::FIELD_SORT_ORDER_NAME => $this->getPositionFieldConfig(50),
                        static::FIELD_IS_DELETE => $this->getIsDeleteFieldConfig(60)
                    ]
                ]
            ]
        ];
    }

    /**
     * Get config for "Price" field
     *
     * @param int $sortOrder
     * @return array
     * @since 101.0.0
     */
    protected function getPriceFieldConfig($sortOrder)
    {
        $field = [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Price'),
                        'componentType' => Field::NAME,
                        'component' => 'Magento_Catalog/js/components/custom-options-component',
                        'formElement' => Input::NAME,
                        'dataScope' => static::FIELD_PRICE_NAME,
                        'dataType' => Number::NAME,
                        'addbefore' => $this->getCurrencySymbol(),
                        'addbeforePool' => $this->productOptionsPrice->prefixesToOptionArray(),
                        'sortOrder' => $sortOrder,
                        'validation' => [
                            'validate-number' => true
                        ],
                    ],
                ],
            ],
        ];
        if ($this->locator->getProduct()->getStoreId() && $this->helper->isScopePrice()) {
            $field['arguments']['data']['config']['imports'] = [
                'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default_price',
                '__disableTmpl' => ['optionId' => false, 'isUseDefault' => false],
            ];
            $field['arguments']['data']['config']['service'] = [
                'template' => 'Magento_Catalog/form/element/helper/custom-option-service',
            ];
        }
        return $field;
    }

    /**
     * Get config for "Price" field for select type.
     *
     * @param int $sortOrder
     * @return array
     */
    private function getPriceFieldConfigForSelectType(int $sortOrder)
    {
        $priceFieldConfig = $this->getPriceFieldConfig($sortOrder);
        $priceFieldConfig['arguments']['data']['config']['template'] = 'Magento_Catalog/form/field';

        if ($this->locator->getProduct()->getStoreId() && $this->helper->isScopePrice()) {
            unset($priceFieldConfig['arguments']['data']['config']['imports']);
            unset($priceFieldConfig['arguments']['data']['config']['service']);
            $optionsPrice = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'imports' => [
                                'optionId' => '${ $.provider }:${ $.parentScope }.option_id',
                                'optionTypeId' => '${ $.provider }:${ $.parentScope }.option_type_id',
                                'isUseDefault' => '${ $.provider }:${ $.parentScope }.is_use_default_price',
                                '__disableTmpl' => ['optionId' => false, 'optionTypeId' => false, 'isUseDefault' => false],
                            ],
                            'service' => [
                                'template' => 'Magento_Catalog/form/element/helper/custom-option-type-service',
                            ],
                        ],
                    ],
                ],
            ];
            return array_replace_recursive($priceFieldConfig, $optionsPrice);
        }


        return $priceFieldConfig;
    }

    /**
     * The getter function to get the locale currency for real application code
     *
     * @return \Magento\Framework\Locale\CurrencyInterface
     *
     * @deprecated 101.0.0
     */
    private function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(CurrencyInterface::class);
        }
        return $this->localeCurrency;
    }
}
