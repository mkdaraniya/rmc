<?php
namespace Swissup\FieldManager\Helper;

use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $inputTypesMap = [
        'date' => [
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class
        ],
        'select' => [
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
        ],
        'multiselect' => [
            'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class
        ],
        'boolean' => [
            'source_model' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
        ]
    ];

    protected $columnTypesMap = [
        'datetime' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATE
        ],
        'decimal' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            'length' => '12,4'
        ],
        'int' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER
        ],
        'text' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT
        ],
        'varchar' => [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255
        ]
    ];

    protected $formElementMap = [
        'text' => 'Magento_Ui/js/form/element/abstract',
        'textarea' => 'Magento_Ui/js/form/element/textarea',
        'select' => 'Magento_Ui/js/form/element/select',
        'boolean' => 'Magento_Ui/js/form/element/select',
        'multiselect' => 'Magento_Ui/js/form/element/multiselect',
        'date' => 'Magento_Ui/js/form/element/date'
    ];

    protected $templateMap = [
        'text' => 'ui/form/element/input',
        'textarea' => 'ui/form/element/textarea',
        'select' => 'ui/form/element/select',
        'boolean' => 'ui/form/element/select',
        'multiselect' => 'ui/form/element/multiselect',
        'date' => 'ui/form/element/date'
    ];

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getColumnType($type)
    {
        if (!empty($this->columnTypesMap[$type])) {
            return $this->columnTypesMap[$type];
        }

        return null;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getBackendModelByInput($type)
    {
        if (!empty($this->inputTypesMap[$type]['backend_model'])) {
            return $this->inputTypesMap[$type]['backend_model'];
        }

        return null;
    }

    /**
     * @param string $type
     * @return string|null
     */
    public function getSourceModelByInput($type)
    {
        if (!empty($this->inputTypesMap[$type]['source_model'])) {
            return $this->inputTypesMap[$type]['source_model'];
        }

        return null;
    }

    public function addScopeHtml($block, $elementIds)
    {
        $fieldObject = $block->getFieldObject();
        if ($fieldObject->getWebsite()->getId() &&
            $fieldObject->getWebsite()->getId() == $block->getRequest()->getParam('website')) {
            foreach ($elementIds as $elementId) {
                $element = $block->getForm()->getElement($elementId);
                if ($element->getDisabled()) continue;

                $id = $element->getId();
                if (strncmp($id, 'default_value_', strlen('default_value_')) === 0) {
                    $id = 'default_value';
                }
                $isDefault = $fieldObject->getData('scope_' . $id) === null;
                if ($isDefault) $element->setDisabled(true);

                $html = $block->getLayout()->createBlock(
                    \Magento\Backend\Block\Template::class
                )->setTemplate(
                    'Swissup_FieldManager::form/scope.phtml'
                )->setData([
                    'element' => $element,
                    'is_default' => $isDefault
                ])->toHtml();

                $element->setAfterElementHtml($html);
            }
        }
    }

    /**
     * Get list of custom attribute codes
     * @param  string $entityType
     * @return array
     */
    public function getCustomAttributeCodes(
        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
    ) {
        $attributeCodes = [];
        $codes = $this->eavConfig->getEntityAttributeCodes($entityType);

        foreach ($codes as $code) {
            $attribute = $this->eavConfig->getAttribute($entityType, $code);
            if ($attribute->getIsUserDefined()) {
                $attributeCodes[] = $code;
            }
        }

        return $attributeCodes;
    }

    /**
     * Get list of attributes by entity
     * @param  string $entityType
     * @return \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    public function getEntityAttributes(
        $entityType = \Magento\Customer\Api\AddressMetadataInterface::ENTITY_TYPE_ADDRESS
    ) {
        return $this->eavConfig->getEntityAttributes($entityType);
    }

    /**
     * Get field Ui component by frontend type
     * @param $field
     * @return string
     */
    public function getFieldUiComponent($field)
    {
        return $this->formElementMap[$field->getFrontendInput()];
    }

    /**
     * Get field template by frontend type
     * @param $field
     * @return string|null
     */
    public function getFieldTemplate($field)
    {
        return $this->templateMap[$field->getFrontendInput()] ?? null;
    }

    /**
     * Get list of forms available for entity
     * @return array
     */
    public function getUsedInForms()
    {
        return [
            'customer_address' => [
                ['label' => __('Customer Address Registration'), 'value' => 'customer_register_address'],
                ['label' => __('Customer Account Address'), 'value' => 'customer_address_edit']
            ],
            'customer' => [
                ['label' => __('Customer Registration'), 'value' => 'customer_account_create'],
                ['label' => __('Customer Account Edit'), 'value' => 'customer_account_edit'],
                ['label' => __('Admin Checkout'), 'value' => 'adminhtml_checkout']
            ]
        ];
    }

    /**
     * Check if field is ignored in config
     *
     * @param  $field
     * @param  string $entityType
     * @return boolean
     */
    public function isFieldIgnored($field, $entityType)
    {
        // only customer fields can be ignored
        if ($entityType == CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER) {
            $ignoredFields = (string) $this->scopeConfig->getValue(
                'customer_field_manager/compatibility/ignore',
                ScopeInterface::SCOPE_STORE
            );

            return in_array($field->getAttributeCode(), explode(',', $ignoredFields));
        }

        return false;
    }

    /**
     * Remove attribute code from value in Magento 2.4
     *
     * @param  $address
     * @return array|null
     */
    public function fixAttributesValues($address)
    {
        $customAttributeCodes = $this->getCustomAttributeCodes();
        if (empty($customAttributeCodes)) {
            return null;
        }

        $customAttributesData = [];
        foreach ($customAttributeCodes as $code) {
            if ($value = $address->getData($code)) {
                $valueParts = preg_split('/\r\n|\r|\n/', $value, -1);

                if ($valueParts[0] === $code) {
                    array_shift($valueParts);
                }

                if (count($valueParts)) {
                    $customAttributesData[$code] = implode(PHP_EOL, $valueParts);
                }
            }
        }

        return !empty($customAttributesData) ? $customAttributesData : null;
    }
}
