<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Model;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogRule\Api\Data\RuleExtensionInterface;
use Magento\CatalogRule\Api\Data\RuleInterface;
use Magento\CatalogRule\Helper\Data;
use Magento\CatalogRule\Model\Data\Condition\Converter;
use Magento\CatalogRule\Model\Indexer\Rule\RuleProductProcessor;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\CatalogRule\Model\Rule\Action\CollectionFactory as RuleCollectionFactory;
use Magento\CatalogRule\Model\Rule\Condition\CombineFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\CatalogRule\Model\ResourceModel\Product\ConditionsToCollectionApplier;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Model\ResourceModel\Iterator;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Catalog Rule data model
 *
 * @method \Magento\CatalogRule\Model\Rule setFromDate(string $value)
 * @method \Magento\CatalogRule\Model\Rule setToDate(string $value)
 * @method \Magento\CatalogRule\Model\Rule setCustomerGroupIds(string $value)
 * @method string getWebsiteIds()
 * @method \Magento\CatalogRule\Model\Rule setWebsiteIds(string $value)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Rule extends \Magento\CatalogRule\Model\Rule
{
    /**
     * @var array
     */
    private $websitesMap;

    /**
     * @var RuleResourceModel
     */
    private $ruleResourceModel;

    /**
     * @return bool
     */
    private function canPreMapProducts()
    {
        $conditions = $this->getConditions();

        // No need to map products if there is no conditions in rule
        if (!$conditions || !$conditions->getConditions()) {
            return false;
        }

        return true;
    }

    /**
     * Callback function for product matching
     *
     * @param array $args
     * @return void
     */
    public function callbackValidateProduct($args)
    {
        $bshelper = ObjectManager::getInstance()->get('Bss\MultiStoreViewPricing\Helper\Data');
        if (!$bshelper->isScopePrice()) {
            return parent::callbackValidateProduct($args);
        }
        
        $product = clone $args['product'];
        $product->setData($args['row']);

        $websites = $this->_getWebsitesMap();
        $results = [];

        foreach ($websites as $websiteId => $defaultStoreId) {
            $website = $this->_storeManager->getWebsite($websiteId);
            $storeIds = $website->getStoreIds();
            foreach ($storeIds as $storeId) {
                $product->setStoreId($storeId);
                $results[$storeId] = $this->getConditions()->validate($product);
            }
        }
        $this->_productIds[$product->getId()] = $results;
    }

    /**
     * @return Data\Condition\Converter
     * @deprecated 100.1.0
     */
    private function getRuleConditionConverter()
    {
        if (null === $this->ruleConditionConverter) {
            $this->ruleConditionConverter = ObjectManager::getInstance()
                ->get(Converter::class);
        }
        return $this->ruleConditionConverter;
    }

    /**
     * Retrieve rule combine conditions model
     *
     * @return \Magento\Rule\Model\Condition\Combine
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        // Load rule conditions if it is applicable
        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                $conditions = $this->serializer->unserialize($conditions);
                if (is_array($conditions) && !empty($conditions)) {
                    if (!empty($conditions['conditions']) && is_array($conditions['conditions'])) {
                        foreach ($conditions['conditions'] as &$conditionArr) {
                            if ($conditionArr['type'] == 'Magento\CatalogRule\Model\Rule\Condition\Product') {
                                $conditionArr['website_ids'] = $this->getWebsiteIds();
                            }
                        }
                    }
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }
}
