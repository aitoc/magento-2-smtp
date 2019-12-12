<?php

namespace Aitoc\Smtp\Model;

use Aitoc\Smtp\Api\Data\LogInterface;
use Aitoc\Smtp\Controller\RegistryConstants;
use Magento\Store\Model\Store;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\MailException;
use Aitoc\Smtp\Model\Config\Options\Status;

/**
 * Class Sender
 * @package Aitoc\Smtp\Model
 */
class Sender
{
    /**
     * @var LogFactory
     */
    private $logFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        \Aitoc\Smtp\Model\LogFactory $logFactory,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Aitoc\Smtp\Model\Config $config
    ) {
        $this->logFactory = $logFactory;
        $this->transportBuilder = $transportBuilder;
        $this->config = $config;
    }

    /**
     * @param $logId
     * @return bool
     */
    public function sendByLogId($logId)
    {
        $log = $this->getCurrentLog($logId);

        if (!$log->getId()) {
            return false;
        }

        $data = $log->getData();
        $data[LogInterface::EMAIL_BODY] = htmlspecialchars_decode($data[LogInterface::EMAIL_BODY]);
        $vars = [];

        if (!$data[LogInterface::EMAIL_BODY]
            || !$data[LogInterface::RECIPIENT_EMAIL]
            || !$data[LogInterface::SENDER_EMAIL]
            || !$data[LogInterface::SUBJECT]
        ) {
            return false;
        }

        $vars[LogInterface::EMAIL_BODY] = $data[LogInterface::EMAIL_BODY];
        $vars[LogInterface::SUBJECT] = $data[LogInterface::SUBJECT];

        $this->transportBuilder
            ->addTo($this->prepareEmailsData($data[LogInterface::RECIPIENT_EMAIL]))
            ->setFrom($this->prepareEmailsData($data[LogInterface::SENDER_EMAIL], true));

        if ($data[LogInterface::BCC]) {
            $this->transportBuilder->addBcc($this->prepareEmailsData($data[LogInterface::BCC]));
        }

        if ($data[LogInterface::CC]) {
            $this->transportBuilder->addCc($this->prepareEmailsData($data[LogInterface::CC]));
        }

        try {
            $this->transportBuilder
                ->setTemplateIdentifier(RegistryConstants::RESEND_EMAIL_TEMPLATE_ID)
                ->setTemplateOptions(['store' => Store::DEFAULT_STORE_ID, 'area' => Area::AREA_FRONTEND])
                ->setTemplateVars($vars);

            $this->transportBuilder->getTransport()->sendMessage();

            $log->setData(LogInterface::STATUS, Status::STATUS_SUCCESS)
                ->setData(LogInterface::STATUS_MESSAGE, '')
                ->save();
        } catch (MailException $e) {
            $log->setData(LogInterface::STATUS, Status::STATUS_FAILED)
                ->setData(LogInterface::STATUS_MESSAGE, $e->getMessage())
                ->save();

            return false;
        }

        return true;
    }

    /**
     * @param $emails
     * @param bool $from
     * @return array|\Zend\Mail\AddressList
     */
    private function prepareEmailsData($emails, $from = false)
    {
        $emailsConverted = [];
        $emails = explode(',', $emails);
        foreach ($emails as $email) {
            $emailData = explode('>', substr($email, 1));

            if ($from) {
                return [
                    'name' => ($emailData[0] == 'Unknown' ? null : $emailData[0]),
                    'email' => $emailData[1],
                ];
            } else {
                $emailsConverted[] = [
                    'name' => ($emailData[0] == 'Unknown' ? null : $emailData[0]),
                    'email' => $emailData[1],
                ];
            }
        }

        return $this->config->getAddressList($emailsConverted);
    }

    /**
     * @return mixed
     */
    public function getCurrentLog($logId)
    {
        return $this->logFactory->create()->getLogById($logId);
    }
}
