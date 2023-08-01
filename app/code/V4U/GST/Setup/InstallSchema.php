<?php
namespace V4U\GST\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'gst_number',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Gst Number',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'gst_number',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Gst Number',
            ]
        );

        $setup->endSetup();
    }
}