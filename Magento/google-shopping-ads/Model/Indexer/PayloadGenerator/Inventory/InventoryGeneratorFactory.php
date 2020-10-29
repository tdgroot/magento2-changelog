<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Inventory;

/**
 * Inventory payload factory
 */
class InventoryGeneratorFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var array
     */
    private $inventoryPool;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $inventoryPool
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager,
        array $inventoryPool = []
    ) {
        $this->objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->inventoryPool = $inventoryPool;
    }

    /**
     * Create Inventory instance
     *
     * @return InventoryGeneratorInterface
     */
    public function create(): InventoryGeneratorInterface
    {
        $inventory = $this->objectManager->create($this->getInventoryClass());
        if (!($inventory instanceof InventoryGeneratorInterface)) {
            throw new \InvalidArgumentException(
                'Inventory should implement InventoryGeneratorInterface'
            );
        }
        return $inventory;
    }

    /**
     * Get inventory class
     *
     * @return string
     */
    private function getInventoryClass(): string
    {
        $inventoryClass = $this->inventoryPool['catalog_inventory'];
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $inventoryClass = $this->inventoryPool['msi'];
        }
        return $inventoryClass;
    }
}
