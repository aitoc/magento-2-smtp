<?php

namespace Aitoc\Smtp\Model;

use Aitoc\Smtp\Api\Data\LogInterface;
use Aitoc\Smtp\Controller\RegistryConstants;

class Log extends \Magento\Framework\Model\AbstractModel implements LogInterface
{
    const LOG_ACTIVE = '1';
    const LOG_INACTIVE = '0';
    const LOG_ID_TYPE_FIELD = 'log_id';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Aitoc\Core\Model\Helpers\Date
     */
    private $date;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Aitoc\Smtp\Model\ResourceModel\Log $resource,
        \Aitoc\Smtp\Model\ResourceModel\Log\Collection $resourceCollection,
        \Aitoc\Smtp\Model\Config $config,
        \Aitoc\Core\Model\Helpers\Date $date,
        array $data = []
    )
    {
        $this->config = $config;
        $this->date = $date;
        parent::__construct($context, $registry, $resource, $resourceCollection);
    }

    /**
     * {@inheritdoc}
     */
    public function _construct()
    {
        $this->_init(\Aitoc\Smtp\Model\ResourceModel\Log::class);
    }

    /**
     * @return int|mixed
     */
    public function getLogId()
    {
        return $this->getData(LogInterface::LOG_ID);
    }

    /**
     * @param int $logId
     * @return $this|LogInterface
     */
    public function setLogId($logId)
    {
        $this->setData(LogInterface::LOG_ID, $logId);

        return $this;
    }

    /**
     * @param $ruleId
     *
     * @return $this
     */
    public function getLogById($logId)
    {
        $resource = $this->getResource();
        $resource->load($this, $logId);

        return $this;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSave()
    {
        parent::beforeSave();

        return $this->updateDate();
    }

    /**
     * @return $this
     */
    private function updateDate()
    {
        return $this;
    }

    /**
     * @param $message
     * @param array $errorData
     * @return $this
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function log($message, $errorData = [])
    {
        if ($this->config->logEnabled()) {
            $logData = $this->getLogData($message);
            $logData[LogInterface::CREATED_AT] = $this->date->getCurrentDate();
            $logData[LogInterface::STATUS] = $errorData ? $errorData[LogInterface::STATUS] : 0;
            $logData[LogInterface::STATUS_MESSAGE] = $errorData ? $errorData[LogInterface::STATUS_MESSAGE] : '';

            $this->setData($logData);
            $this->_resource->save($this);
        }

        return $this;
    }

    /**
     * @param $message
     * @return array
     */
    private function getLogData($message)
    {
        $result = [];

        if (is_object($message) && is_a($message, '\\Symfony\\Component\\Mime\\Email')) {
            $result[LogInterface::SUBJECT] = (string)$message->getSubject();
            $result[LogInterface::SENDER_EMAIL] = $this->getEmailsFromAddressList($message->getFrom());
            $result[LogInterface::RECIPIENT_EMAIL] = $this->getEmailsFromAddressList($message->getTo());
            $result[LogInterface::BCC] = $this->getEmailsFromAddressList($message->getBcc());
            $result[LogInterface::CC] = $this->getEmailsFromAddressList($message->getCc());

            $body = $message->getHtmlBody();
            if ($body === null || $body === '') {
                $body = $message->getTextBody();
            }
            $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$body);

            return $result;
        } else if ($this->config->isNewSender(RegistryConstants::VERSION_COMPARISON_OLD_MAIL)) {
            $subject = '';
            if (method_exists($message, 'getSubject')) {
                $subject = (string)$message->getSubject();
            }
            $result[LogInterface::SUBJECT] = $subject;
            if (method_exists($message, 'getFrom')) {
                $result[LogInterface::SENDER_EMAIL] = $this->getEmailsFromAddressList($message->getFrom());
            }
            if (method_exists($message, 'getTo')) {
                $result[LogInterface::RECIPIENT_EMAIL] = $this->getEmailsFromAddressList($message->getTo());
            }
            if ((!isset($result[LogInterface::SENDER_EMAIL]) || $result[LogInterface::SENDER_EMAIL] === '')
                || (!isset($result[LogInterface::RECIPIENT_EMAIL]) || $result[LogInterface::RECIPIENT_EMAIL] === '')) {
                if (method_exists($message, 'getHeaders')) {
                    $headers = $message->getHeaders();
                    if (is_object($headers) && method_exists($headers, 'toArray')) {
                        $headers = $headers->toArray();
                    }
                    if ((!isset($result[LogInterface::SENDER_EMAIL]) || $result[LogInterface::SENDER_EMAIL] === '') && isset($headers['From'])) {
                        $fromHeader = $headers['From'];
                        $result[LogInterface::SENDER_EMAIL] = is_array($fromHeader) ? ($fromHeader[0] ?? '') : (string)$fromHeader;
                    }
                    if ((!isset($result[LogInterface::RECIPIENT_EMAIL]) || $result[LogInterface::RECIPIENT_EMAIL] === '') && isset($headers['To'])) {
                        $toHeader = $headers['To'];
                        if (is_array($toHeader) && isset($toHeader['append'])) {
                            unset($toHeader['append']);
                        }
                        $result[LogInterface::RECIPIENT_EMAIL] = $this->getEmailsFromAddressList($toHeader);
                    }
                }
            }
            if (method_exists($message, 'getBcc')) {
                $result[LogInterface::BCC] = $this->getEmailsFromAddressList($message->getBcc());
            }
            if (method_exists($message, 'getCc')) {
                $result[LogInterface::CC] = $this->getEmailsFromAddressList($message->getCc());
            }

            $bodyText = '';
            if (method_exists($message, 'getBodyText')) {
                $bodyText = $message->getBodyText();
            } elseif (method_exists($message, 'getBody')) {
                $body = $message->getBody();
                if (is_object($body) && method_exists($body, 'getRawContent')) {
                    $bodyText = $body->getRawContent();
                } elseif (is_object($body) && method_exists($body, 'getBody')) {
                    $bodyText = $body->getBody();
                }
            }
            $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$bodyText);

            return $result;
        }

        if (is_object($message) && is_a($message, '\\Laminas\\Mail\\Message')) {
            $result[LogInterface::SUBJECT] = method_exists($message, 'getSubject') ? (string)$message->getSubject() : '';
            if (method_exists($message, 'getFrom')) {
                $result[LogInterface::SENDER_EMAIL] = $this->getEmailsFromAddressList($message->getFrom());
            }
            if (method_exists($message, 'getTo')) {
                $result[LogInterface::RECIPIENT_EMAIL] = $this->getEmailsFromAddressList($message->getTo());
            }
            if (method_exists($message, 'getBcc')) {
                $result[LogInterface::BCC] = $this->getEmailsFromAddressList($message->getBcc());
            }
            if (method_exists($message, 'getCc')) {
                $result[LogInterface::CC] = $this->getEmailsFromAddressList($message->getCc());
            }

            $emailBody = null;
            if (method_exists($message, 'getBodyHtml')) {
                $emailBody = $message->getBodyHtml();
                if (is_object($emailBody) && method_exists($emailBody, 'getRawContent')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars($emailBody->getRawContent());
                } elseif ($emailBody !== null) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$emailBody);
                }
            }
            if (!isset($result[LogInterface::EMAIL_BODY]) && method_exists($message, 'getBody')) {
                $body = $message->getBody();
                if (is_object($body) && method_exists($body, 'getRawContent')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars($body->getRawContent());
                } elseif (is_object($body) && method_exists($body, 'getBody')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$body->getBody());
                }
            }

            if (!isset($result[LogInterface::EMAIL_BODY])) {
                $result[LogInterface::EMAIL_BODY] = '';
            }

            return $result;
        }

        if (method_exists($message, 'getHeaders')) {
            $headers = $message->getHeaders();
            if (is_object($headers) && method_exists($headers, 'toArray')) {
                $headers = $headers->toArray();
            }
            $result[LogInterface::SUBJECT] = isset($headers['Subject'])
                ? (is_array($headers['Subject']) ? ($headers['Subject'][0] ?? '') : (string)$headers['Subject'])
                : '';
            $result[LogInterface::SENDER_EMAIL] = isset($headers['From'])
                ? (is_array($headers['From']) ? ($headers['From'][0] ?? '') : (string)$headers['From'])
                : '';
            if (isset($headers['To'])) {
                $recipient = $headers['To'];
                if (isset($recipient['append'])) {
                    unset($recipient['append']);
                }
                $result[LogInterface::RECIPIENT_EMAIL] = $this->getEmailsFromAddressList($recipient);
            }
            if (isset($headers['Cc'])) {
                $cc = $headers['Cc'];
                if (isset($cc['append'])) {
                    unset($cc['append']);
                }
                $result[LogInterface::CC] = $this->getEmailsFromAddressList($cc);
            }
            if (isset($headers['Bcc'])) {
                $bcc = $headers['Bcc'];
                if (isset($bcc['append'])) {
                    unset($bcc['append']);
                }
                $result[LogInterface::BCC] = $this->getEmailsFromAddressList($bcc);
            }

            $emailBody = null;
            if (method_exists($message, 'getBodyHtml')) {
                $emailBody = $message->getBodyHtml();
                if (is_object($emailBody) && method_exists($emailBody, 'getRawContent')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars($emailBody->getRawContent());
                } elseif ($emailBody !== null) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$emailBody);
                }
            }
            if (!isset($result[LogInterface::EMAIL_BODY]) && method_exists($message, 'getBody')) {
                $body = $message->getBody();
                if (is_object($body) && method_exists($body, 'getRawContent')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars($body->getRawContent());
                } elseif (is_object($body) && method_exists($body, 'getBody')) {
                    $result[LogInterface::EMAIL_BODY] = htmlspecialchars((string)$body->getBody());
                }
            }

            if (!isset($result[LogInterface::EMAIL_BODY])) {
                $result[LogInterface::EMAIL_BODY] = '';
            }

            return $result;
        }

        $result[LogInterface::SUBJECT] = '';
        $result[LogInterface::SENDER_EMAIL] = '';
        $result[LogInterface::RECIPIENT_EMAIL] = '';
        $result[LogInterface::BCC] = '';
        $result[LogInterface::CC] = '';
        $result[LogInterface::EMAIL_BODY] = '';
        return $result;
    }

    /**
     * @param $emails
     * @return string
     */
    private function getEmailsFromAddressList($emails)
    {
        $result = [];

        if (!$emails) {
            return '';
        }

        if (is_string($emails)) {
            return trim((string)$emails);
        }

        if (is_object($emails) && method_exists($emails, 'toArray')) {
            $normalized = $emails->toArray();
            foreach ($normalized as $email => $name) {
                $name = $name ?: 'Unknown';
                $result[] = "<" . $name . ">" . $email;
            }
            return implode(',', $result);
        }

        if (is_array($emails)) {
            foreach ($emails as $email) {
                $address = '';
                $name = 'Unknown';

                if (is_object($email)) {
                    if (method_exists($email, 'getEmail')) {
                        $address = $email->getEmail();
                        $name = $email->getName() ?: $name;
                    } elseif (method_exists($email, 'getAddress')) {
                        $address = $email->getAddress();
                        $name = $email->getName() ?: $name;
                    }
                } elseif (is_array($email)) {
                    $address = isset($email['email']) ? $email['email'] : (isset($email[0]) ? $email[0] : '');
                    $name = isset($email['name']) ? $email['name'] : $name;
                } elseif (is_string($email)) {
                    $address = $email;
                }

                if ($address) {
                    $result[] = "<" . $name . ">" . $address;
                }
            }
            return implode(',', $result);
        }

        if (is_object($emails)) {
            $address = '';
            $name = 'Unknown';
            if (method_exists($emails, 'getEmail')) {
                $address = $emails->getEmail();
                $name = $emails->getName() ?: $name;
            } elseif (method_exists($emails, 'getAddress')) {
                $address = $emails->getAddress();
                $name = $emails->getName() ?: $name;
            }
            if ($address) {
                return "<" . $name . ">" . $address;
            }
        }

        return '';
    }
}
