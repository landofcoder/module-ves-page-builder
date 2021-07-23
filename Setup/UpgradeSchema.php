<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_PageBuilder
 * @copyright  Copyright (c) 2016 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\PageBuilder\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $tableName = $installer->getTable('ves_blockbuilder_widget');

        if (version_compare($context->getVersion(), '1.0.0', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == false) {
                /**
                 * Create table 'ves_blockbuilder_widget'
                 */
                $setup->getConnection()->dropTable($tableName);
                $table = $installer->getConnection()->newTable($tableName)->addColumn(
                    'widget_key',
                    Table::TYPE_TEXT,
                    100,
                    ['nullable' => false, 'primary' => true],
                    'Widget Key'
                )->addColumn(
                    'block_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Block Profile Id'
                ) ->addColumn(
                    'widget_shortcode',
                    Table::TYPE_TEXT,
                    '64k',
                    ['nullable' => false],
                    'Widget Shortcode'
                )->addColumn(
                    'created',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Row Creation Time'
                )->setComment(
                    'Ves Block Builder Widgets Table'
                );
                $installer->getConnection()->createTable($table);
            }

            // Get module table
            $tableName = $setup->getTable('ves_blockbuilder_block');
            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                // Declare data
                $columns = [
                    'image' => [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'image URL',
                        'length'  => 150
                    ],
                    'description' => [
                        'type' => Table::TYPE_TEXT,
                        'nullable' => true,
                        'comment' => 'Block Description',
                        'length'  => '64k'
                    ]
                ];

                $connection = $setup->getConnection();
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }

            }
        }
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $tableName = $installer->getTable('ves_blockbuilder_widget');
            $tableNameBlock = $installer->getTable('ves_blockbuilder_block');
            if ($setup->getConnection()->isTableExists($tableName) == true) {
                $installer->getConnection()->modifyColumn(
                    $tableName,
                    'widget_shortcode',
                        [
                            'type'           => Table::TYPE_TEXT,
                            'nullable'       => false,
                            'length'         => "10M"
                        ]
                    );
            }
            $columns = [
                'layout_update_selected' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Page Custom Layout File',
                    'length'  => 150
                ]
            ];
            $connection = $setup->getConnection();
            foreach ($columns as $name => $definition) {
                $connection->addColumn($tableNameBlock, $name, $definition);
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /**
             * Create table 'ves_blockbuilder_product'
             */
            $installer->getConnection()->addColumn(
                $installer->getTable('ves_blockbuilder_product'),
                'position',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'default' => 0,
                    'comment' => 'position',
                    'after' => 'store_id'
                ]
            );
        }
        $installer->endSetup();
    }
}
