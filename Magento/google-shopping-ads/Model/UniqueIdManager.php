<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

/**
 * A model to retrieve unique Magento ID
 */
class UniqueIdManager
{
    const UNIQUE_ID_PATH = 'sales_channels/sales_channel_integration/unique_id';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writer;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writer = $writer;
        $this->reinitableConfig = $reinitableConfig;
    }

    /**
     * Gets current unique Magento ID
     *
     * @return string
     */
    public function get() : string
    {
        $uniqueId = $this->scopeConfig->getValue(self::UNIQUE_ID_PATH);

        if (!$uniqueId) {
            $uniqueId = uniqid();
            $this->writer->save(self::UNIQUE_ID_PATH, $uniqueId);
            $this->reinitableConfig->reinit();
        }

        return $uniqueId;
    }
}
