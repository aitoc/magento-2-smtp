<?php

namespace Aitoc\Smtp\Cron;

use Exception;
use Aitoc\Core\Model\Helpers\Date;
use Aitoc\Smtp\Model\Config;
use Aitoc\Smtp\Model\ResourceModel\Log\CollectionFactory;
use Psr\Log\LoggerInterface;


class Clear
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Date
     */
    private $date;

    public function __construct(
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Date $date,
        Config $config
    ) {
        $this->logger = $logger;
        $this->date = $date;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        if (!$this->config->enabled()) {
            return $this;
        }

        $cleanDays = $this->config->cleanLogDays();

        if ($cleanDays) {
            $logCollection = $this->collectionFactory->create()
                ->addFieldToFilter('created_at', [
                    'lteq' => $this->date->getCurrentDateBeforeDays($cleanDays)
                ]);

            foreach ($logCollection as $log) {
                try {
                    $log->delete();
                } catch (Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }

        return $this;
    }
}
