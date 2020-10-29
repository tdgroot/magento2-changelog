<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Cron;

/**
 * Cronjob for site verification in Google
 */
class SiteVerification
{
    /**
     * Number of allowed failed attempts
     */
    const ATTEMPTS = 5;

    /**
     * Verification config data model for a website
     */
    const VERIFICATION_CONFIG = [
        'code' => '',
        'isVerified' => false,
        'attempts' => 0
    ];

    /**
     * Path to settings
     */
    const PATH_VERIFICATION_CONFIGS = 'sales_channels/sales_channel_integration/verification_configs';

    /**
     * URL for cache docs
     */
    const CACHE_DOCS_URL = 'https://docs.magento.com/m2/ce/user_guide/system/cache-management.html';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClient;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writer;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceDataRetriever
     */
    private $serviceDataRetriever;

    /**
     * Related cache types which should be cleaned
     *
     * @var array
     */
    private $relatedCacheTypes;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever
     * @param array $relatedCacheTypes
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer,
        \Magento\GoogleShoppingAds\Model\ServiceDataRetriever $serviceDataRetriever,
        array $relatedCacheTypes = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serviceClient = $serviceClient;
        $this->cacheTypeList = $cacheTypeList;
        $this->writer = $writer;
        $this->serviceDataRetriever = $serviceDataRetriever;
        $this->relatedCacheTypes = $relatedCacheTypes;
    }

    /**
     * Retrieve verification configs from database
     *
     * @param array $onBoardedWebsites
     * @return array
     */
    private function getVerificationConfigs(array $onBoardedWebsites) : array
    {
        $verificationConfigs = $this->scopeConfig->getValue(
            self::PATH_VERIFICATION_CONFIGS
        );

        if ($verificationConfigs) {
            $verificationConfigs = json_decode($verificationConfigs, true);
            foreach (array_keys($verificationConfigs) as $key) {
                if (!isset($onBoardedWebsites[$key])) {
                    unset($verificationConfigs[$key]);
                }
            }
        } else {
            $verificationConfigs = [];
        }
        return $verificationConfigs;
    }

    /**
     * Get website ids config from SaaS
     *
     * @return array
     */
    private function getOnBoardedWebsites() : array
    {
        $websiteConfigs = $this->serviceDataRetriever->getWebsiteConfigs();
        $onBoardedWebsites = [];
        if ($websiteConfigs) {
            $websiteConfigs = json_decode($websiteConfigs, true);
            foreach ($websiteConfigs as $websiteConfig) {
                if (!empty($websiteConfig['channelAttributes']['webSiteId'])) {
                    $onBoardedWebsites[$websiteConfig['channelAttributes']['webSiteId']] = [
                        'channelId' => $websiteConfig['channelId'],
                        'websiteClaimed' => filter_var($websiteConfig['websiteClaimed'], FILTER_VALIDATE_BOOLEAN),
                    ];
                }
            }
        }
        return $onBoardedWebsites;
    }

    /**
     * Site verification and claiming
     */
    public function execute()
    {
        $onBoardedWebsites = $this->getOnBoardedWebsites();
        $verificationConfigs = $this->getVerificationConfigs($onBoardedWebsites);

        foreach ($onBoardedWebsites as $websiteId => $channelConfig) {
            $channelId = $channelConfig['channelId'];
            if (!isset($verificationConfigs[$websiteId])) {
                $verificationConfigs[$websiteId] = self::VERIFICATION_CONFIG;
            }

            if (!$verificationConfigs[$websiteId]['isVerified']) {
                $verificationConfigs[$websiteId]['isVerified'] = $channelConfig['websiteClaimed'];

                if ($verificationConfigs[$websiteId]['attempts'] < self::ATTEMPTS
                    && !$verificationConfigs[$websiteId]['isVerified']
                    && $verificationConfigs[$websiteId]['code']) {
                    $this->serviceClient->requestVerification($channelId);
                    $verificationConfigs[$websiteId]['attempts']++;
                }

                if (!$verificationConfigs[$websiteId]['code']) {
                    $verificationConfigs[$websiteId]['code'] = $this->retrieveCode($channelId);
                }

                $this->writer->save(self::PATH_VERIFICATION_CONFIGS, json_encode($verificationConfigs));
                foreach ($this->relatedCacheTypes as $cacheType) {
                    $this->cacheTypeList->cleanType($cacheType);
                }
            }
        }
    }

    /**
     * Get verification code from SaaS
     *
     * @param string $channelId
     * @return string
     */
    private function retrieveCode(string $channelId) : string
    {
        try {
            $result = $this->serviceClient->getVerificationCode($channelId);
            return json_decode($result['body'], true)['verification-code'];
        } catch (\Exception $e) {
            return '';
        }
    }
}
