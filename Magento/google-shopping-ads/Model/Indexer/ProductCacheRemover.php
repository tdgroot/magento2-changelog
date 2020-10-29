<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\StateInterface;

/**
 * Class that removes disabled products from cache
 */
class ProductCacheRemover
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CacheInterface $cache,
        StateInterface $cacheState,
        CollectionFactory $collectionFactory
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Deletes removed products from cache
     *
     * @param array $productIds
     */
    public function execute(array $productIds)
    {
        if ($this->cacheState->isEnabled(\Magento\GoogleShoppingAds\Model\Cache\Type::TYPE_IDENTIFIER)) {
            $productSkus = $this->getProductSkus($productIds);
            foreach ($productSkus as $productSku) {
                $this->cache->remove(\Magento\GoogleShoppingAds\Model\Cache\Type::CACHE_TAG . $productSku);
            }
        }
    }

    /**
     * @param array $productIds
     * @return array
     */
    private function getProductSkus(array $productIds): array
    {
        $collection = $this->collectionFactory->create();
        if (count($productIds)) {
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);
            return array_column($collection->getData(), 'sku');
        }
        return [];
    }
}
