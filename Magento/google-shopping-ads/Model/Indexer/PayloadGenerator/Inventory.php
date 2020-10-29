<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator;

use Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\InventoryGeneratorFactory;

/**
 * Inventory payload generator
 */
class Inventory implements \Magento\GoogleShoppingAds\Model\Indexer\PayloadGeneratorInterface
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory\InventoryGeneratorFactory
     */
    private $inventoryGeneratorFactory;

    /**
     * @param InventoryGeneratorFactory $inventoryGeneratorFactory
     */
    public function __construct(InventoryGeneratorFactory $inventoryGeneratorFactory)
    {
        $this->inventoryGeneratorFactory = $inventoryGeneratorFactory;
    }

    /**
     * @inheritdoc
     */
    public function generate(
        array $products,
        int $websiteId
    ) : array {
        return $this->inventoryGeneratorFactory->create()->generateInventory($products, $websiteId);
    }
}
