<?php

namespace Aitoc\Smtp\Plugin\Framework\Mail;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Mail\TransportInterfaceFactory as TransportInterfaceFactoryCore;
use Aitoc\Smtp\Model\Config;
use Aitoc\Smtp\Controller\RegistryConstants;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * TransportInterface Plugin
 */
class TransportInterfaceFactory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Aitoc\Smtp\Model\Config
     */
    private $config;
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        Config $config,
        ProductMetadataInterface $productMetadata
    ) {
        $this->objectManager = $objectManager;
        $this->config = $config;
        $this->productMetadata = $productMetadata;
    }

    /**
     * Create Class instance with Specified Parameters
     *
     * @param $subject TransportInterfaceFactoryCore
     * @param $proceed \Callable
     * @param array $data
     * @return \Magento\Framework\Mail\TransportInterface
     */
    public function aroundCreate($subject, $proceed, array $data = [])
    {
        if ($this->config->enabled()) {
            if ($data && isset($data[\Aitoc\Smtp\Controller\RegistryConstants::IS_TEST_FIELD_ARRAY])) {
                unset($data[RegistryConstants::IS_TEST_FIELD_ARRAY]);
                $config = $data;
                $data['message'] = $this->objectManager->create(\Magento\Framework\Mail\Message::class);
            } else {
                $config = $this->config->getFullConfig();
            }

            $data = array_merge($data, ['config' => $config]);

            // Detect Magento version
            $version = $this->productMetadata->getVersion();
            if (version_compare($version, '2.4.8', '>=')) {
                // Use Symfony for Magento 2.4.8+
                return $this->objectManager->create(
                    \Aitoc\Smtp\Model\Framework\Mail\TransportSymfony::class,
                    $data
                );
            }
            // Default Laminas for < 2.4.8
            return $this->objectManager->create(
                \Aitoc\Smtp\Model\Framework\Mail\TransportLaminas::class,
                $data
            );
        }

        return $proceed($data);
    }
}
