<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory;

/**
 * An interface for inventory payload generators
 */
interface InventoryGeneratorInterface
{
    /**
     * Generate inventory payload
     *
     * @param array $products
     * @param int $websiteId
     * @return string
     */
    public function generateInventory(
        array $products,
        int $websiteId
    ) : array;
}
