<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Cron;

use \Magento\GoogleShoppingAds\Controller\Adminhtml\Index\MagentoGatewayCallback;

/**
 * Cronjob for retrieving gtag code
 */
class GTagRetriever
{
    /**
     * Path for gtag data
     */
    const PATH_GTAG_CONFIG = 'sales_channels/sales_channel_integration/gtag';

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
     * @var array
     */
    private $relatedCacheTypes;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param array $relatedCacheTypes
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer,
        array $relatedCacheTypes = []
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->serviceClient = $serviceClient;
        $this->cacheTypeList = $cacheTypeList;
        $this->writer = $writer;
        $this->relatedCacheTypes = $relatedCacheTypes;
    }

    /**
     * GTag retrieving and saving into DB
     */
    public function execute()
    {
        try {
            if ($this->scopeConfig->getValue(MagentoGatewayCallback::PATH_MAGENTO_KEY)
                && !$this->scopeConfig->getValue(self::PATH_GTAG_CONFIG)) {
                $response = $this->serviceClient->getAdwordsAccount();
                if (isset($response['code']) && isset($response['body']) && ($response['code'] == 200)) {
                    $adwordsAccount = json_decode($response['body'], true);
                    if ($adwordsAccount && isset($adwordsAccount['googleEventSnippet'])) {
                        $this->saveGTag($adwordsAccount['googleEventSnippet']);
                    }
                }
            }
        } catch (\Exception $e) {
            //just do nothing and let try next run
            return;
        }
    }

    /**
     * Saves GTag to config
     *
     * @param string $gtag
     */
    private function saveGTag(string $gtag)
    {
        $this->writer->save(self::PATH_GTAG_CONFIG, $gtag);
        foreach ($this->relatedCacheTypes as $cacheType) {
            $this->cacheTypeList->cleanType($cacheType);
        }
    }
}
