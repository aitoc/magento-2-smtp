<?php

namespace Aitoc\Smtp\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Log extends AbstractDb
{
    /**
     *  Smtp Log Store Table Name
     */
    const AITOC_SMTP_LOG_TABLE_NAME = 'aitoc_smtp_log';

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(
            self::AITOC_SMTP_LOG_TABLE_NAME,
            \Aitoc\Smtp\Model\Log::LOG_ID_TYPE_FIELD
        );
    }
}
