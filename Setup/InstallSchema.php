<?php

namespace Aitoc\Smtp\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    const AITOC_SMTP_LOG_TABLE_NAME = 'aitoc_smtp_log';

    /**
     * @inheritDoc
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (!$installer->tableExists(self::AITOC_SMTP_LOG_TABLE_NAME)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(self::AITOC_SMTP_LOG_TABLE_NAME))
                ->addColumn(
                    'log_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Log Id'
                )->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    null,
                    ['nullable' => true, 'default' => null],
                    'Created At'
                )
                ->addColumn(
                    'subject',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => false]
                )
                ->addColumn(
                    'email_body',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false]
                )
                ->addColumn(
                    'sender_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    120,
                    ['nullable' => false]
                )
                ->addColumn(
                    'recipient_email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    120,
                    ['nullable' => false]
                )
                ->addColumn(
                    'cc',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    120,
                    ['nullable' => true]
                )
                ->addColumn(
                    'bcc',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    120,
                    ['nullable' => true]
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false]
                )->addColumn(
                    'status_message',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable' => true]
                )->setComment('Aitoc SMTP Log');

            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
