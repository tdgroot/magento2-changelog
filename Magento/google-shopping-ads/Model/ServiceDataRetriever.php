<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * A model to retrieve data from our service
 */
class ServiceDataRetriever
{
    /**
     * @var ServiceClient
     */
    private $serviceClient;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ServiceClient $serviceClient
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->serviceClient = $serviceClient;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get website configs with channels body
     *
     * @return string
     */
    public function getWebsiteConfigs() : string
    {
        try {
            if ($this->scopeConfig->getValue(
                \Magento\GoogleShoppingAds\Controller\Adminhtml\Index\MagentoGatewayCallback::PATH_MAGENTO_KEY
            )) {
                $channels = $this->serviceClient->getChannels();
                if (isset($channels['code']) && isset($channels['body']) && ($channels['code'] == 200)) {
                    return $channels['body'];
                }
            }
        } catch (\Exception $e) {
            //just do nothing and let return empty array json
            return '[]';
        }
        return '[]';
    }

    /**
     * Retrieve saved mapping data
     *
     * @return string
     */
    public function getMappings() : string
    {
        try {
            $mappings = [];
            $configs = json_decode($this->getWebsiteConfigs(), true);
            if (count($configs)) {
                foreach ($configs as $config) {
                    $mappings = $this->getConfigMappings($config['channelId']);
                }
                return $mappings;
            }
        } catch (\Exception $e) {
            //just do nothing and let return empty string
            return '';
        }
        return '';
    }

    /**
     * Get config mappings by config ID
     *
     * @param string $configId
     * @return string
     */
    private function getConfigMappings(string $configId) : string
    {
        $mappings = [];
        $mapping = $this->serviceClient->getMapping($configId);
        if (isset($mapping['code']) && isset($mapping['body']) && ($mapping['code'] == 200)) {
            $mappingArray = json_decode($mapping['body'], true);
            foreach ($mappingArray as $mappingItem) {
                if ($mappingItem['channelAttributeCode'] === 'brand') {
                    foreach ($mappingItem['magentoAttributes'] as $attribute) {
                        $mappings[] = $attribute['magentoAttributeCode'];
                    }
                }
            }
        }

        return json_encode($mappings);
    }
}
