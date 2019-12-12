<?php

namespace Aitoc\Smtp\Model\Config\Options;

use Magento\Framework\Option\ArrayInterface;

class Provider implements ArrayInterface
{
    /**
     * @var \Aitoc\Smtp\Model\Providers
     */
    private $providers;

    public function __construct(
        \Aitoc\Smtp\Model\Providers $providers
    ) {
        $this->providers = $providers;
    }

    public function toOptionArray()
    {
        $result = [];

        $result[] = [
            'value' => 'none',
            'label' => 'None'
        ];
        
        foreach ($this->providers->getAllProviders() as $key => $providerData) {
            $result[] = [
                'value' => $key,
                'label' => $providerData['label']
            ];
        }

        return $result;
    }
}
