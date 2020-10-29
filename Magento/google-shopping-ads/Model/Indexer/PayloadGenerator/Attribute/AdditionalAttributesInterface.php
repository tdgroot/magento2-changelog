<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model\Indexer\PayloadGenerator\Attribute;

use Magento\Store\Api\Data\StoreInterface;

/**
 * Interface for additional attributes generators
 */
interface AdditionalAttributesInterface
{
    /**
     * Sets additional attributes for google
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param StoreInterface $store
     * @param \Magento\Catalog\Model\Product $parent
     * @return array
     */
    public function getAttributes(
        \Magento\Catalog\Model\Product $product,
        StoreInterface $store,
        \Magento\Catalog\Model\Product $parent = null
    ) : array;
}
