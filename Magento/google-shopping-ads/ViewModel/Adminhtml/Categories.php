<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying all categories
 */
class Categories implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    const CATEGORY_DELIMITER = ' > ';

    /**
     * @var \Magento\Catalog\Api\CategoryListInterface
     */
    private $categoryList;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $categoryRepository;

    /**
     * @param \Magento\Catalog\Api\CategoryListInterface $categoryList
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        \Magento\Catalog\Api\CategoryListInterface $categoryList,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    ) {
        $this->categoryList = $categoryList;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Gets model data
     *
     * @return string
     */
    public function getData() : string
    {
        $data = [];

        $categories = $this->categoryList->getList($this->searchCriteriaBuilder->create());

        foreach ($categories->getItems() as $category) {
            $categoryName = $this->getFullCategoryName($category);
            if ($categoryName) {
                $data[] = [
                    'value' => $category->getId(),
                    'label' => $categoryName
                ];
            }
        }

        return json_encode($data);
    }

    /**
     * Gets category name joined with its parents names
     *
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return string
     */
    private function getFullCategoryName(\Magento\Catalog\Api\Data\CategoryInterface $category)
    {
        $parentId = $category->getParentId();
        if (!$parentId || $parentId === 1) {
            return '';
        }
        try {
            $parentCategory = $this->categoryRepository->get($parentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return '';
        }
        $parentsName = $this->getFullCategoryName($parentCategory);
        return $parentsName ? ($parentsName . self::CATEGORY_DELIMITER . $category->getName()) : $category->getName();
    }
}
