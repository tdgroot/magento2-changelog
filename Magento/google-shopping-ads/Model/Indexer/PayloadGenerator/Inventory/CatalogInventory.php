<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory;

/**
 * Catalog Inventory payload generator
 */
class CatalogInventory implements InventoryGeneratorInterface
{
    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaInterfaceFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    private $stockConfigurationInterface;

    /**
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     * @param \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaInterfaceFactory
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfigurationInterface
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaInterfaceFactory,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfigurationInterface
    ) {
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaInterfaceFactory = $stockItemCriteriaInterfaceFactory;
        $this->stockConfigurationInterface = $stockConfigurationInterface;
    }

    /**
     * @inheritdoc
     */
    public function generateInventory(
        array $products,
        int $websiteId
    ): array {
        $feeds = [];
        $displayOutOfStock = $this->stockConfigurationInterface->isShowOutOfStock() ? '1' : '0';
        foreach ($products as $product) {
            $feeds[$product->getSku()] = $this->map($product, $displayOutOfStock);
        }

        return $feeds;
    }

    /**
     * Map product and stock data in one array
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $displayOutOfStock
     * @return array
     */
    private function map(\Magento\Catalog\Model\Product $product, string $displayOutOfStock): array
    {
        $criteria = $this->stockItemCriteriaInterfaceFactory->create();
        $criteria->setProductsFilter($product->getId());
        $stocks = $this->stockItemRepository->getList($criteria)->getItems();
        $stock = array_shift($stocks);
        $qty = $stock->getQty();
        $qty = ($qty == (int)$qty) ? (int)$qty : (float)$qty;
        if ($product->getTypeId() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $qty = $stock->getIsInStock() ? 100 : 0;
        }
        $productAvailable = $displayOutOfStock === '1' || ($displayOutOfStock === '0' && ($qty !== null && $qty > 0))
            ? 1
            : 0;
        $map = [
            'entityId' => $product->getSku(),
            'magentoId' => $product->getId(),
            'inventory' => [
                'qty' => $qty,
                'configuration' => [
                    'status' => $stock->getIsInStock(),
                    'manageStock' => $stock->getManageStock(),
                    'threshold' => $stock->getMinQty(),
                    'productAvailable' => $productAvailable
                ]
            ]
        ];

        return $map;
    }
}
