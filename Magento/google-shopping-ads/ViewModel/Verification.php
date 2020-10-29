<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel;

/**
 * Responsible for displaying Google verification tag
 */
class Verification implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Get site verification tag
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getTag() : string
    {
        $tag = '';
        $verificationConfigs = $this->scopeConfig->getValue(
            \Magento\GoogleShoppingAds\Cron\SiteVerification::PATH_VERIFICATION_CONFIGS
        );
        if ($verificationConfigs) {
            $verificationConfigs = json_decode($verificationConfigs, true);
            $websiteId = $this->storeManager->getWebsite()->getId();
            $tag = isset($verificationConfigs[$websiteId]) && isset($verificationConfigs[$websiteId]['code'])
                ? $verificationConfigs[$websiteId]['code'] : '';
        }
        return $tag;
    }
}
