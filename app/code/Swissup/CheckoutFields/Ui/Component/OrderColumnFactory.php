<?php

namespace Swissup\CheckoutFields\Ui\Component;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderColumnFactory extends \Magento\Catalog\Ui\Component\ColumnFactory
{
    /**
     * @{inheritdocs}
     */
    public function create($attribute, $context, array $config = [])
    {
        $config = array_merge(
            [
                'label' => __($attribute->getDefaultFrontendLabel()),
                'dataType' => $this->getDataType($attribute),
                'add_field' => true,
                'visible' => $attribute->getIsVisibleInGrid(),
                'filter' => null
            ],
            $config
        );

        if ($attribute->usesSource()) {
            $config['options'] = $attribute->getSource()->getAllOptions();
            foreach ($config['options'] as &$optionData) {
                $optionData['__disableTmpl'] = true;
            }
        }

        $config['component'] = $this->getJsComponent($config['dataType']);

        if ($config['dataType'] === 'date') {
            $timezone = ObjectManager::getInstance()->get(TimezoneInterface::class);
            $dateConfig = [
                'timezone' => $timezone->getDefaultTimezone(),
                'dateFormat' => $timezone->getDateFormat(\IntlDateFormatter::MEDIUM),
                'options' => ['showsTime' => false]
            ];

            $config += $dateConfig;
        }

        $columnName = $attribute->getAttributeCode();
        $arguments = ['data' => ['config' => $config], 'context' => $context];

        return $this->componentFactory->create($columnName, 'column', $arguments);
    }
}
