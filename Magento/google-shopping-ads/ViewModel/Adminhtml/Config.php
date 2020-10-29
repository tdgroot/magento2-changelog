<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying different configs of Magento
 */
class Config implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const FRONTEND_URL = 'sales_channels/sales_channel_integration/frontend_url';

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
     * Returns url of react application.
     *
     * @return string
     */
    public function getFrontendUrl() : string
    {
        return trim((string)$this->scopeConfig->getValue(self::FRONTEND_URL), '/');
    }

    /**
     * Returns admin session lifetime
     *
     * @return int
     */
    public function getAdminSessionLifetime() : int
    {
        return (int)$this->scopeConfig->getValue(\Magento\Backend\Model\Auth\Session::XML_PATH_SESSION_LIFETIME);
    }
}
