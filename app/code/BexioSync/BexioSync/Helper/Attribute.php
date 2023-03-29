<?php
namespace BexioSync\BexioSync\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Attribute extends AbstractHelper
{
       public function createProductAttribute($attributeCode,$attributeName)
       {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // instance of object manager

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/bexiosync.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $logger->info(__METHOD__);
            $logger->info("Product Attribute Creating ...");

            try{
                $eavConfig = $objectManager->get('\Magento\Eav\Model\Config');
                $eavEavSetupFactory = $objectManager->get('\Magento\Eav\Setup\EavSetupFactory');
                $attributeSetFactory = $objectManager->get('Magento\Eav\Model\Entity\Attribute\SetFactory');

                $attribute = $eavConfig->getAttribute('catalog_product', $attributeCode);
        
                if (!$attribute || !$attribute->getAttributeId()) {

                    /** @var ProductSetup $productSetup */
                    $productSetup = $eavEavSetupFactory->create();
        
                    $productEntity = $productSetup->getEntityTypeId('catalog_product');
                    $attributeSetId = $productSetup->getDefaultAttributeSetId($productEntity);
        
                    /** @var $attributeSet AttributeSet */
                    $attributeSet = $attributeSetFactory->create();
                    $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        
                    
                    /**
                     * Add attributes to the eav_attribute
                     */
                    $productSetup->addAttribute(
                        \Magento\Catalog\Model\Product::ENTITY,
                        $attributeCode,
                        [
                            'group'        => 'General',
                            'type'         => 'varchar',
                            'backend'      => '',
                            'frontend'     => '',
                            'label'        => $attributeName,
                            'input'        => 'text',
                            'global'       => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                            'visible'      => true,
                            'required'     => false,
                            'user_defined' => false,
                            'default'      => '',
                            'searchable'   => false,
                            'filterable'   => false,
                            'comparable'   => false,
                            'unique'       => false,
                            'visible_on_front'        => false,
                            'used_in_product_listing' => true
                        ]
                    );
        
                }


            }catch(\Exception $e) {
                $logger->critical($e);
                return $logger->critical($e);
            }

       }
}