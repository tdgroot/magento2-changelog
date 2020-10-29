<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel;

/**
 * Retrieves gtag config from DB
 */
class GtagConfig implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get GTag conversion
     *
     * @return string
     */
    public function getConversion() : string
    {
        return (string)$this->scopeConfig->getValue(\Magento\GoogleShoppingAds\Cron\GTagRetriever::PATH_GTAG_CONFIG);
    }

    /**
     * Get GTag id
     *
     * @return string
     */
    public function getId() : string
    {
        $id = '';
        $config = $this->getConversion();
        if ($config) {
            $id = explode('/', $config)[0];
        }
        return $id;
    }
}
