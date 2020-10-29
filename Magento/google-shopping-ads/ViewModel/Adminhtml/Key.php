<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying Magento Key
 */
class Key implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Path to key bypass settings
     */
    const PATH_BYPASS_KEY_CHECK = 'sales_channels/sales_channel_integration/bypass_key_check';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $client;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $client
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\GoogleShoppingAds\Model\ServiceClient $client
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->client = $client;
    }

    /**
     * Gets model data
     *
     * @return string
     */
    public function getData() : string
    {
        return $this->scopeConfig->getValue(
            \Magento\GoogleShoppingAds\Controller\Adminhtml\Index\MagentoGatewayCallback::PATH_MAGENTO_KEY
        ) ?: '';
    }

    /**
     * Returns whether entered key is valid
     *
     * @return string
     */
    public function isKeyValid() : string
    {
        $bypass = $this->scopeConfig->getValue(self::PATH_BYPASS_KEY_CHECK);
        return json_encode($bypass ? true : $this->client->isKeyValid());
    }
}
