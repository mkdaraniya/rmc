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
 * @package    Bss_MultiStoreViewPricingCatalogRule
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MultiStoreViewPricingCatalogRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $table = $installer->getConnection()
                ->newTable($installer->getTable('catalogrule_product_store'))
                ->addColumn(
                    'rule_product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Rule Product Id'
                )
                ->addColumn(
                    'rule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Rule Id'
                )
                ->addColumn(
                    'from_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'From Time'
                )
                ->addColumn(
                    'to_time',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'To time'
                )
                ->addColumn(
                    'customer_group_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Customer Group Id'
                )
                ->addColumn(
                    'product_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Product Id'
                )
                ->addColumn(
                    'action_operator',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    10,
                    ['default' => 'to_fixed'],
                    'Action Operator'
                )
                ->addColumn(
                    'action_amount',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    [12, 4],
                    ['nullable' => false, 'default' => '0.0000'],
                    'Action Amount'
                )
                ->addColumn(
                    'action_stop',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Action Stop'
                )
                ->addColumn(
                    'sort_order',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Sort Order'
                )
                ->addColumn(
                    'website_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Website Id'
                )
                ->addColumn(
                    'store_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Store Id'
                )
                ->addIndex(
                    $installer->getIdxName(
                        'catalogrule_product_store',
                        ['rule_id', 'from_time', 'to_time', 'website_id', 'store_id', 'customer_group_id', 'product_id', 'sort_order'],
                        true
                    ),
                    ['rule_id', 'from_time', 'to_time', 'website_id', 'store_id', 'customer_group_id', 'product_id', 'sort_order'],
                    ['type' => 'unique']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['customer_group_id']),
                    ['customer_group_id']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['website_id']),
                    ['website_id']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['store_id']),
                    ['store_id']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['from_time']),
                    ['from_time']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['to_time']),
                    ['to_time']
                )
                ->addIndex(
                    $installer->getIdxName('catalogrule_product_store', ['product_id']),
                    ['product_id']
                )
                ->setComment('CatalogRule Product Store');

            $installer->getConnection()->createTable($table);
            $this->addReplicaTable($setup, 'catalogrule_product_store', 'catalogrule_product_store_replica');
        }
        $installer->endSetup();
    }


    /**
     * Add the replica table for existing one.
     *
     * @param SchemaSetupInterface $setup
     * @param string $existingTable
     * @param string $replicaTable
     * @return void
     */
    private function addReplicaTable(SchemaSetupInterface $setup, $existingTable, $replicaTable)
    {
        $sql = sprintf(
            'CREATE TABLE IF NOT EXISTS %s LIKE %s',
            $setup->getTable($replicaTable),
            $setup->getTable($existingTable)
        );
        $setup->getConnection()->query($sql);
    }
}
