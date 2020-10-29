<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer;

/**
 * Class that detects changes in product payload
 */
class ChangeDetector
{
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    private $cacheState;

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\App\Cache\StateInterface $cacheState
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
    }

    /**
     * Filters out unchanged products
     *
     * @param array $payload
     */
    public function filterUnchanged(array &$payload)
    {
        if ($this->cacheState->isEnabled(\Magento\GoogleShoppingAds\Model\Cache\Type::TYPE_IDENTIFIER)) {
            foreach ($payload as $productId => $productData) {
                $hash = (string)crc32(json_encode($productData));
                $cacheHash = $this->cache->load(
                    \Magento\GoogleShoppingAds\Model\Cache\Type::CACHE_TAG . $productId
                );
                if ($cacheHash === $hash) {
                    unset($payload[$productId]);
                } else {
                    $this->cache->save(
                        $hash,
                        \Magento\GoogleShoppingAds\Model\Cache\Type::CACHE_TAG . $productId,
                        [\Magento\GoogleShoppingAds\Model\Cache\Type::CACHE_TAG]
                    );
                }
            }
        }
    }
}
