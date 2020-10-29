<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory;

use Magento\Framework\ObjectManagerInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Multi-Source Inventory payload generator
 */
class MultiSourceInventory implements InventoryGeneratorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var GetAssignedStockIdForWebsiteInterface|null
     */
    private $getStockByWebsite;

    /**
     * @var GetStockItemConfigurationInterface|null
     */
    private $getStockItemConfiguration;

    /**
     * @var GetProductSalableQtyInterface|null
     */
    private $getSalableQuantity;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowed;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @param ObjectManagerInterface $objectmanager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function generateInventory(
        array $products,
        int $websiteId
    ): array {
        $feeds = [];
        foreach ($products as $product) {
            $feeds[$product->getSku()] = $this->map($product, $websiteId);
        }
        return $feeds;
    }

    /**
     * Map product and stock data in one array
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function map(\Magento\Catalog\Model\Product $product, int $websiteId): array
    {
        $websiteCode = $this->storeManager->getWebsite($websiteId)->getCode();
        $stockId = $this->getStockIdForWebsite()->execute($websiteCode);
        $salableQty = 0;
        if ($this->getSourceManagementAllowed()->execute($product->getTypeId())) {
            $salableQty = $this->getGetProductSalableQty()->execute($product->getSku(), $stockId);
        } elseif ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE
            && $this->getIsProductSalable()->execute($product->getSku(), $stockId)) {
            $salableQty = 100;
        }
        $isProductSalable = $salableQty ? 1 : 0;
        try {
            $stockConfiguration = $this->getStockItemConfiguration()->execute($product->getSku(), $stockId);
        } catch (\Magento\InventoryConfigurationApi\Exception\SkuIsNotAssignedToStockException $e) {
            $defaultStockId = $this->getDefaultStockProvider()->getId();
            $stockConfiguration = $this->getStockItemConfiguration()->execute(
                $product->getSku(),
                $defaultStockId
            );
        }
        $map = [
            'entityId' => $product->getSku(),
            'magentoId' => $product->getId(),
            'inventory' => [
                'qty' => $salableQty,
                'configuration' => [
                    'status' => $isProductSalable,
                    'manageStock' => $stockConfiguration->isManageStock(),
                    'threshold' => $stockConfiguration->getMinQty(),
                    'productAvailable' => $isProductSalable
                ]
            ]
        ];
        return $map;
    }

    /**
     * Get \Magento\InventorySalesApi\Model\GetAssignedStockIdForWebsiteInterface object
     * Will get class via ObjectManager if it's not stored in the $this->getStockByWebsite private variable
     *
     * @return GetAssignedStockIdForWebsiteInterface
     */
    private function getStockIdForWebsite(): GetAssignedStockIdForWebsiteInterface
    {
        if (null === $this->getStockByWebsite) {
            $this->getStockByWebsite = $this->objectManager->create(GetAssignedStockIdForWebsiteInterface::class);
        }

        return $this->getStockByWebsite;
    }

    /**
     * Get \Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface object
     * Will get class via ObjectManager if it's not stored in the $this->getStockItemConfiguration private variable
     *
     * @return GetStockItemConfigurationInterface
     */
    private function getStockItemConfiguration(): GetStockItemConfigurationInterface
    {
        if (null === $this->getStockItemConfiguration) {
            $this->getStockItemConfiguration = $this->objectManager->create(GetStockItemConfigurationInterface::class);
        }

        return $this->getStockItemConfiguration;
    }

    /**
     * Get \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface class
     * Will get class via ObjectManager if it's not stored in the $this->getSalableQuantity private variable
     *
     * @return GetProductSalableQtyInterface
     */
    private function getGetProductSalableQty(): GetProductSalableQtyInterface
    {
        if (null === $this->getSalableQuantity) {
            $this->getSalableQuantity = $this->objectManager->create(GetProductSalableQtyInterface::class);
        }

        return $this->getSalableQuantity;
    }

    /**
     * Get \Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface class
     * Will get class via ObjectManager if it's not stored in the $this->defaultStockProvider private variable
     *
     * @return DefaultStockProviderInterface
     */
    private function getDefaultStockProvider(): DefaultStockProviderInterface
    {
        if (null === $this->defaultStockProvider) {
            $this->defaultStockProvider = $this->objectManager->create(DefaultStockProviderInterface::class);
        }

        return $this->defaultStockProvider;
    }

    /**
     * Get IsSourceItemManagementAllowedForProductTypeInterface class
     * Will get class via ObjectManager if it's not stored in the $this->isSourceItemManagementAllowed private variable
     *
     * @return IsSourceItemManagementAllowedForProductTypeInterface
     */
    private function getSourceManagementAllowed(): IsSourceItemManagementAllowedForProductTypeInterface
    {
        if (null === $this->isSourceItemManagementAllowed) {
            $this->isSourceItemManagementAllowed = $this->objectManager
                ->create(IsSourceItemManagementAllowedForProductTypeInterface::class);
        }

        return $this->isSourceItemManagementAllowed;
    }

    /**
     * Get \Magento\InventorySalesApi\Api\IsProductSalableInterface class
     * Will get class via ObjectManager if it's not stored in the $this->isProductSalable private variable
     *
     * @return IsProductSalableInterface
     */
    private function getIsProductSalable(): IsProductSalableInterface
    {
        if (null === $this->isProductSalable) {
            $this->isProductSalable = $this->objectManager->create(IsProductSalableInterface::class);
        }

        return $this->isProductSalable;
    }
}
