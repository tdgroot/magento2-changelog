<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Model;

use Magento\Catalog\Api\Data\ProductAttributeInterface;

/**
 * Creates new attribute and assigns it to all attribute sets
 */
class AttributeCreator
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeManagementInterface
     */
    private $productAttributeManagement;

    /**
     * @var \Magento\Catalog\Api\AttributeSetRepositoryInterface
     */
    private $attributeSetRepository;

    /**
     * @var \Magento\Eav\Api\AttributeGroupRepositoryInterface
     */
    private $attributeGroupRepository;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagement
     * @param \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param \Magento\Eav\Api\AttributeGroupRepositoryInterface $attributeGroupRepository
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Catalog\Api\ProductAttributeManagementInterface $productAttributeManagement,
        \Magento\Catalog\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        \Magento\Eav\Api\AttributeGroupRepositoryInterface $attributeGroupRepository,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {

        $this->productAttributeManagement = $productAttributeManagement;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Create a new attribute and add it to all existing attribute sets
     *
     * @param ProductAttributeInterface $attribute
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function create(ProductAttributeInterface $attribute) : string
    {
        $attribute = $this->getAttribute($attribute);

        try {
            $attribute = $this->attributeRepository->save($attribute);

            $attributeSets = $this->attributeSetRepository->getList($this->searchCriteriaBuilder->create())->getItems();
            foreach ($attributeSets as $attributeSet) {
                $this->searchCriteriaBuilder->setFilterGroups([]);
                $this->searchCriteriaBuilder->addFilter('attribute_set_id', $attributeSet->getId());
                $attributeGroups = $this->attributeGroupRepository->getList($this->searchCriteriaBuilder->create())
                    ->getItems();
                $attributeGroup = array_shift($attributeGroups);
                if ($attributeGroup) {
                    $this->productAttributeManagement->assign(
                        $attributeSet->getId(),
                        $attributeGroup->getId(),
                        $attribute,
                        0
                    );
                }
            }
        } catch (\Exception $e) {
            // The attribute already existed and there were no changes to save
        }

        return $attribute->getAttributeCode();
    }

    /**
     * Get existing attribute if it exists
     *
     * @param ProductAttributeInterface $attribute
     * @return ProductAttributeInterface
     */
    private function getAttribute(ProductAttributeInterface $attribute) : ProductAttributeInterface
    {
        //If attribute exists and is invisible and not user created we make it visible
        try {
            $existingAttribute = $this->attributeRepository->get($attribute->getAttributeCode());
            if (!$existingAttribute->getIsVisible()) {
                $existingAttribute->setIsVisible(true);
                return $existingAttribute;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            //If attribute does not exist we just keep creating it
            return $attribute;
        }
        return $attribute;
    }
}
