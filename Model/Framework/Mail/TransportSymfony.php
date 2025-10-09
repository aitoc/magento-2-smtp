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
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

class TransportSymfony implements \Magento\Framework\Mail\TransportInterface
{
    const DEFAULT_LOCAL_CLIENT_HOSTNAME = 'localhost';
    const TEST_MESSAGE_SUBJECT = 'Aitoc SMTP Test';
    const TEST_MESSAGE_BODY =
        "Now, your store uses an Aitoc SMTP. Please, hit ‘Save Config’ to use this connection.";

    /**
     * @var Mailer
     */
    private $smtp;
    /**
     * @var
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

    /**
     * @param $message
     * @param SenderResolver $senderResolver
     * @param Config $aitConfig
     * @param From $from
     * @param LogFactory $logFactory
     * @param Registry $registry
     * @param array $config
     */
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

        $dsn = $this->buildDsn($config);
        $transport = \Symfony\Component\Mailer\Transport::fromDsn($dsn);
        $this->smtp = new \Symfony\Component\Mailer\Mailer($transport);
        $this->message = $message;
        $this->setFrom();
    }

    /**
     * @param array $config
     * @return string
     */
    private function buildDsn(array $config): string
    {
        $host = $config['host'] ?? self::DEFAULT_LOCAL_CLIENT_HOSTNAME;
        $port = (int)($config['port'] ?? 465);
        $ssl = $config['ssl'] ?? null;
        $authType = $config['auth'] ?? null;

        if ($port === 465 || $ssl === 'ssl') {
            $scheme = 'smtps';
        } else {
            $scheme = 'smtp';
        }

        $auth = '';
        if (!empty($config['username'])) {
            $auth = urlencode($config['username']) . ':' . urlencode((string)($config['password'] ?? '')) . '@';
        }

        $query = [];
        if ($authType && $authType !== 'none') {
            $query['auth_mode'] = $authType;
        }

        if ($scheme === 'smtp' && ($port === 587 || $ssl === 'tls')) {
            $query['encryption'] = 'tls';
        }

        $dsn = sprintf('%s://%s%s:%d', $scheme, $auth, $host, $port);
        if (!empty($query)) {
            $dsn .= '?' . http_build_query($query);
        }

        return $dsn;
    }

    /**
     * @return $this
     */
    public function setFrom()
    {
        $fromData = $this->fromResolver->getFrom();
        if ($fromData) {
            if (!$this->message->getFrom()) {
                $this->message->setFromAddress($this->aitConfig->getNewAddress($fromData));
            }
        }
        return $this;
    }

    /**
     * @return void
     * @throws MailException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendMessage()
    {
        $logDisabled = $this->registry->registry(RegistryConstants::CURRENT_RULE) ? true : false;

        try {
            $this->message = $this->aitConfig->prepareMessageToSend($this->getMessage());

            if (method_exists($this->message, 'getSymfonyMessage')) {
                $symfonyMessage = $this->message->getSymfonyMessage();
            } else {
                $symfonyMessage = $this->convertToSymfonyEmail($this->message);
            }

            if (!($symfonyMessage instanceof Email)) {
                $symfonyMessage = $this->convertToSymfonyEmail($this->message);
            }

            $symfonyMessage = $this->ensureBasicHeaders($symfonyMessage);

            if ($this->aitConfig->isBlockedDelivery()) {
                $modifiedRecipient = $this->modifyTo($symfonyMessage);
                if (!$modifiedRecipient) {
                    $errorData = [
                        'status' => Status::STATUS_BLOCKED,
                        'status_message' => 'Debug mode'
                    ];
                    if (!$logDisabled) {
                        $this->getLoger()->log($symfonyMessage, $errorData);
                    }
                    return;
                }
                $symfonyMessage = $modifiedRecipient;
            }

            $this->smtp->send($symfonyMessage);

            if (!$logDisabled) {
                $this->getLoger()->log($symfonyMessage);
            }
        } catch (\Exception $e) {
            $errorData = [
                'status' => Status::STATUS_FAILED,
                'status_message' => $e->getMessage()
            ];
            if (!$logDisabled) {
                $this->getLoger()->log($symfonyMessage, $errorData);
            }
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @param Email $message
     * @return false|Email
     */
    public function modifyTo(Email $message)
    {
        $newEmails = [];
        foreach ($message->getTo() as $to) {
            $email = $to->getAddress();
            $name = $to->getName();
            if ($this->aitConfig->needToBlockEmail($email)) {
                continue;
            }
            $newEmails[] = $name ? "$name <$email>" : $email;
        }
        if (!$newEmails) {
            return false;
        }
        $message->to(...$newEmails);
        return $message;
    }

    /**
     * @param $to
     * @return true
     * @throws MailException
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function testSend($to)
    {
        try {
            $email = new Email();

            $fromData = $this->fromResolver->getFrom();
            if ($fromData) {
                $email->from(new \Symfony\Component\Mime\Address($fromData['email'], $fromData['name']));
            } else {
                $senderData = $this->aitConfig->getSenderData();
                if ($senderData) {
                    $email->from(new \Symfony\Component\Mime\Address($senderData['email'], $senderData['name']));
                }
            }

            $email->to($to)
                  ->subject(self::TEST_MESSAGE_SUBJECT)
                  ->text(self::TEST_MESSAGE_BODY);

            $this->smtp->send($email);
            return true;
        } catch (\Exception $e) {
            throw new MailException(new Phrase($e->getMessage()), $e);
        }
    }

    /**
     * @return MessageInterface
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return \Aitoc\Smtp\Model\Log
     */
    private function getLoger()
    {
        return $this->logFactory->create();
    }

    /**
     * Ensure email has From header
     *
     * @param Email $email
     * @return Email
     */
    private function ensureFromHeader(Email $email): Email
    {
        if (!$email->getFrom()) {
            $fromData = $this->fromResolver->getFrom();
            if ($fromData) {
                $email->from(new \Symfony\Component\Mime\Address($fromData['email'], $fromData['name']));
            } else {
                $senderData = $this->aitConfig->getSenderData();
                if ($senderData) {
                    $email->from(new \Symfony\Component\Mime\Address($senderData['email'], $senderData['name']));
                }
            }
        }
        return $email;
    }

    /**
     * @param Email $email
     * @return Email
     */
    private function ensureBasicHeaders(Email $email): Email
    {
        $email = $this->ensureFromHeader($email);

        if (!$email->getTo()) {
            $to = $this->message->getTo();
            if (is_array($to)) {
                foreach ($to as $toAddress) {
                    $email->addTo(new \Symfony\Component\Mime\Address($toAddress->getEmail(), $toAddress->getName()));
                }
            }
        }

        if (!$email->getSubject()) {
            $subject = (string) $this->message->getSubject();
            if ($subject) {
                $email->subject($subject);
            }
        }

        return $email;
    }

    /**
     * @param $message
     * @return Email
     * @throws MailException
     */
    protected function convertToSymfonyEmail($message)
    {
        $email = new Email();

        $from = $message->getFrom();
        if ($from) {
            if (is_array($from)) {
                foreach ($from as $fromAddress) {
                    $email->from(new \Symfony\Component\Mime\Address($fromAddress->getEmail(), $fromAddress->getName()));
                }
            } else {
                $email->from(new \Symfony\Component\Mime\Address($from->getEmail(), $from->getName()));
            }
        } else {
            $fromData = $this->fromResolver->getFrom();
            if ($fromData) {
                $email->from(new \Symfony\Component\Mime\Address($fromData['email'], $fromData['name']));
            } else {
                $senderData = $this->aitConfig->getSenderData();
                if ($senderData) {
                    $email->from(new \Symfony\Component\Mime\Address($senderData['email'], $senderData['name']));
                }
            }
        }

        $to = $message->getTo();
        if (is_array($to)) {
            foreach ($to as $toAddress) {
                $email->addTo(new \Symfony\Component\Mime\Address($toAddress->getEmail(), $toAddress->getName()));
            }
        }

        $email->subject((string) $message->getSubject());

        $body = $message->getBody();
        if ($body) {
            $content = $body->getBody();
            $subtype = $body->getMediaSubtype();

            if ($subtype === 'html') {
                $email->html($content);
            } else {
                $email->text($content);
            }
        }

        $cc = $message->getCc();
        if (is_array($cc)) {
            foreach ($cc as $ccAddress) {
                $email->addCc(new \Symfony\Component\Mime\Address($ccAddress->getEmail(), $ccAddress->getName()));
            }
        }

        $bcc = $message->getBcc();
        if (is_array($bcc)) {
            foreach ($bcc as $bccAddress) {
                $email->addBcc(new \Symfony\Component\Mime\Address($bccAddress->getEmail(), $bccAddress->getName()));
            }
        }

        $replyTo = $message->getReplyTo();
        if (is_array($replyTo)) {
            foreach ($replyTo as $replyToAddress) {
                $email->addReplyTo(new \Symfony\Component\Mime\Address($replyToAddress->getEmail(), $replyToAddress->getName()));
            }
        }

        return $email;
    }

    
}
