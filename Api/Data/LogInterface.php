<?php

namespace Aitoc\Smtp\Api\Data;

/**
 * Interface LogInterface
 * @package Aitoc\Smtp\Api\Data
 */
interface LogInterface
{
    /**
     * Constants
     */
    const TABLE_NAME = 'aitoc_smtp_log';

    const LOG_ID = 'log_id';
    const CREATED_AT = 'created_at';
    const SUBJECT = 'subject';
    const EMAIL_BODY = 'email_body';
    const SENDER_EMAIL = 'sender_email';
    const RECIPIENT_EMAIL = 'recipient_email';
    const CC = 'cc';
    const BCC = 'bcc';
    const STATUS = 'status';
    const STATUS_MESSAGE = 'status_message';

    /**
     * @return int
     */
    public function getLogId();

    /**
     * @param int $logId
     * @return LogInterface
     */
    public function setLogId($logId);
}
