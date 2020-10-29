<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying required website data
 */
class Websites implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    private $currencyFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->currencyFactory = $currencyFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Gets model data
     *
     * @return string
     */
    public function getData() : string
    {
        $stores = $this->getStoreData();
        $websites = $this->storeManager->getWebsites(true);

        $data = [];

        foreach ($websites as $website) {
            if (!$website->getName()) {
                continue;
            }
            $currency = $this->currencyFactory->create()->load(
                $this->scopeConfig->getValue(
                    \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_CURRENCY_OPTIONS_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getCode()
                )
            );
            $data[] = [
                'id' => $website->getId(),
                'name' => $website->getName(),
                'url' => $this->scopeConfig->getValue(
                    \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_SECURE_BASE_URL,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getCode()
                ),
                'currency' => [
                    'code' => $currency->getCurrencyCode(),
                    'symbol' => $currency->getCurrencySymbol(),
                ],
                'timezone' => $this->scopeConfig->getValue(
                    \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_LOCALE_TIMEZONE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getCode()
                ),
                'country' => $this->scopeConfig->getValue(
                    \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_COUNTRY_DEFAULT,
                    \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                    $website->getCode()
                ),
                'stores' => isset($stores[$website->getId()]) ? $stores[$website->getId()] : []
            ];
        }

        return json_encode($data);
    }

    /**
     * Get store id, name and code
     *
     * @return array
     */
    private function getStoreData() : array
    {
        $data = [];
        $stores = $this->storeManager->getStores(true);
        foreach ($stores as $store) {
            $data[$store->getWebsiteId()][] = [
                'id' => $store->getId(),
                'name' => $store->getName(),
                'code' => $store->getCode(),
            ];
        }
        return $data;
    }
}
