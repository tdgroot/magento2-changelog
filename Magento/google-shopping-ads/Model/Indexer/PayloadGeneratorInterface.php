<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer;

/**
 * An interface for payload generators
 */
interface PayloadGeneratorInterface
{
    /**
     * Generate payload
     *
     * @param array $products
     * @param int $websiteId
     * @return array
     */
    public function generate(
        array $products,
        int $websiteId
    ) : array;
}
