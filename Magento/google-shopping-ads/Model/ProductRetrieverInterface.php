<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * General interface for product retrievers
 */
interface ProductRetrieverInterface
{
    /**
     * Retrieves products to be sent to Google.
     *
     * @param array $excludedIds
     * @param array $filterIds
     * @param int $page
     * @param int $pageSize
     * @return ProductInterface[]
     * @throws \Magento\Framework\Exception\InputException
     */
    public function retrieve(
        array $excludedIds,
        array $filterIds,
        int $page = 1,
        int $pageSize = 1000
    ) : array;
}
