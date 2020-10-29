<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Retrieves configurable products to be sent to Google.
 */
class ConfigurableProductRetriever implements ProductRetrieverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrder
     */
    private $sortOrder;

    /**
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SortOrder $sortOrder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SortOrder $sortOrder
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->sortOrder = $sortOrder;
    }

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
        int $pageSize = 5
    ) : array {
        if (!count($filterIds)) {
            return [];
        }
        $this->searchCriteriaBuilder->addFilter(
            'type_id',
            \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE
        );

        $this->searchCriteriaBuilder->addFilter('entity_id', $filterIds, 'in');

        if (count($excludedIds)) {
            $this->searchCriteriaBuilder->addFilter('entity_id', $excludedIds, 'nin');
        }

        $sortOrder = $this->sortOrder->setField('updated_at')
            ->setDirection(\Magento\Framework\Api\SortOrder::SORT_DESC);
        $this->searchCriteriaBuilder->setSortOrders([$sortOrder]);

        $this->searchCriteriaBuilder->setCurrentPage($page);
        $this->searchCriteriaBuilder->setPageSize($pageSize);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $configurables = $this->productRepository->getList($searchCriteria)->getItems();
        $childProducts = [];
        foreach ($configurables as $configurable) {
            foreach ($configurable->getTypeInstance()->getUsedProducts($configurable) as $childProduct) {
                if (!in_array($childProduct->getId(), $excludedIds)) {
                    $childProducts[] = $childProduct;
                }
            }
        }
        return $childProducts;
    }
}
