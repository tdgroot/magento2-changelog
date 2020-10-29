<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying version and edition of Magento
 */
class ProductMetadata implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const MODULE_NAME = 'Magento_GoogleShoppingAds';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {

        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
    }

    /**
     * Gets Magento version
     *
     * @return string
     */
    public function getVersion() : string
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Gets Magento edition
     *
     * @return string
     */
    public function getEdition() : string
    {
        return $this->productMetadata->getEdition();
    }

    /**
     * Gets module version
     *
     * @return string
     */
    public function getModuleVersion() : string
    {
        $module = $this->moduleList->getOne(self::MODULE_NAME);
        return $module['setup_version'];
    }
}
