<?php

namespace Aitoc\Smtp\Model\Framework\Mail;

use Aitoc\Smtp\Controller\RegistryConstants;
use Aitoc\Smtp\Model\Config;
use Aitoc\Smtp\Model\Config\Options\Status;
use Aitoc\Smtp\Model\LogFactory;
use Aitoc\Smtp\Model\Resolver\From;
use Magento\Email\Model\Template\SenderResolver;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\MessageInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class Transport implements \Magento\Framework\Mail\TransportInterface
{
    const DEFAULT_LOCAL_CLIENT_HOSTNAME = 'localhost';
    const TEST_MESSAGE_SUBJECT = 'Aitoc SMTP Test';
    const TEST_MESSAGE_BODY =
        "Now, your store uses an Aitoc SMTP. Please, hit ‘Save Config’ to use this connection.";

    /**
     * @var \Laminas\Mail\Transport\Smtp
     */
    private $smtp;

    /**
     * @var MessageInterface
     */
    private $message;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Config
     */
    private $aitConfig;

    /**
     * @var SenderResolver
     */
    private $senderResolver;

    /**
     * @var From
     */
    private $fromResolver;

    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        $message,
        SenderResolver $senderResolver,
        Config $aitConfig,
        From $from,
        LogFactory $logFactory,
        Registry $registry,
        array $config = []
    ) {
        $this->config = $config;
        $this->aitConfig = $aitConfig;
        $this->senderResolver = $senderResolver;
        $this->fromResolver = $from;
        $this->logFactory = $logFactory;
        $this->registry = $registry;
        $this->smtp = new \Laminas\Mail\Transport\Smtp($this->prepareOptions($config));
        $this->message = $message;
        $this->setFrom();
    }

    /**
     * @return $this
     */
    public function setFrom()
    {
        $fromData = $this->fromResolver->getFrom();
        $message = \Laminas\Mail\Message::fromString($this->message->getRawMessage());

        if ($fromData) {
            if (($message instanceof \Laminas\Mail\Message && !$message->getFrom()->count())
                || (!array_key_exists("From", $message->getHeaders()->toArray()))
            ) {
                $this->message->setFromAddress($this->aitConfig->getNewAddress($fromData));
            }
        }

        return $this;
    }

    /**
     * @param $config
     * @return \Laminas\Mail\Transport\SmtpOptions
     */
    private function prepareOptions($config)
    {
        if (!isset($config['name']) || !$config['name']) {
            $config['name'] = self::DEFAULT_LOCAL_CLIENT_HOSTNAME;
        }

        $options = new \Laminas\Mail\Transport\SmtpOptions();
        $options->setName($config['name'] ?? '');
        $options->setHost($config['host'] ?? '');
        $options->setPort($config['port'] ?? 465);
        $connectionConfig = [];

        if (isset($config['auth']) && $config['auth'] != '') {
            $options->setConnectionClass($config['auth']);
            $connectionConfig = [
                'username' => isset($config['username']) ? $config['username'] : '',
                'password' => isset($config['password']) ? $config['password'] : ''
            ];
        }

        if (isset($config['ssl']) && $config['ssl']) {
            $connectionConfig['ssl'] = $config['ssl'];
        }

        if (!empty($connectionConfig)) {
            $options->setConnectionConfig($connectionConfig);
        }

        return $options;
    }

    /**
     * @inheritdoc
     */
    public function sendMessage()
    {
        $logDisabled = $this->registry->registry(RegistryConstants::CURRENT_RULE) ? true : false;

        try {
            $this->message = $this->aitConfig->prepareMessageToSend($this->getMessage());

            if ($this->aitConfig->isNewSender(RegistryConstants::VERSION_COMPARISON_OLD_MAIL)) {
                $message = \Laminas\Mail\Message::fromString($this->message->getRawMessage())->setEncoding('utf-8');
            } else {
                $message = $this->message;
            }

            if ($this->aitConfig->isBlockedDelivery()) {
                $modifiedRecipient = $this->modifyTo();

                if (!$modifiedRecipient) {
                    $errorData = [
                        'status' => Status::STATUS_BLOCKED,
                        'status_message' => 'Debug mode'
                    ];

                    if (!$logDisabled) {
                        $this->getLoger()->log($message, $errorData);
                        return;
                    }
                }

                $message = $modifiedRecipient;
            }

            if ($message) {
                $this->smtp->send($message);
            }

            if (!$logDisabled) {
                $this->getLoger()->log($message);
            }
        } catch (\Exception $e) {
            $errorData = [
                'status' => Status::STATUS_FAILED,
                'status_message' => $e->getMessage()
            ];

            if (!$logDisabled) {
                $this->getLoger()->log($message, $errorData);
            }
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @return \Aitoc\Smtp\Model\Log
     */
    private function getLoger()
    {
        return $this->logFactory->create();
    }

    /**
     * @return bool|\Laminas\Mail\Message
     */
    public function modifyTo()
    {
        if ($this->aitConfig->isNewSender(RegistryConstants::VERSION_COMPARISON_OLD_MAIL)) {
            $message = \Laminas\Mail\Message::fromString($this->message->getRawMessage())->setEncoding('utf-8');
        } else {
            $message = $this->message;
        }

        $toEmails = $message->getTo();

        $newEmails = [];

        if ($toEmails) {
            foreach ($toEmails as $email) {
                $name = '';
                if ($email instanceof \Laminas\Mail\Address\AddressInterface) {
                    $name = $email->getName();
                    $email = $email->getEmail();
                }

                if ($this->aitConfig->needToBlockEmail($email)) {
                    continue;
                }

                $newEmails[] = [
                    'email' => $email,
                    'name' => $name
                ];
            }
        }

        if (!$newEmails) {
            return false;
        }

        $addressList = $this->aitConfig->getAddressList($newEmails);
        $message->setTo($addressList);

        return $message;
    }

    /**
     * @param $to
     * @return bool
     * @throws MailException
     */
    public function testSend($to)
    {
        try {
            $result = false;
            $this->message = $this->aitConfig->prepareMessageToSend($this->getMessage(), true);
            $this->message
                ->addTo($to)
                ->setSubject(self::TEST_MESSAGE_SUBJECT)
                ->setBodyText(__(self::TEST_MESSAGE_BODY));

            if ($this->aitConfig->isNewSender(RegistryConstants::VERSION_COMPARISON_OLD_MAIL)) {
                $message = \Laminas\Mail\Message::fromString($this->message->getRawMessage())->setEncoding('utf-8');
            } else {
                $message = $this->message;
            }

            $this->smtp->send($message);

            return $result;
        } catch (\Exception $e) {
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @inheritdoc
     */
    public function getMessage()
    {
        return $this->message;
    }
}
