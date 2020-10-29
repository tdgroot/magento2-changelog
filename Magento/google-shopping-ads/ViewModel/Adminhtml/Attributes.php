<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\ViewModel\Adminhtml;

/**
 * ViewModel responsible for displaying attribute config
 */
class Attributes implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * Gets model data
     *
     * @return string
     */
    public function getData() : string
    {
        $attributeArray = $this->getAttributeArray();
        return json_encode($attributeArray);
    }

    /**
     * Gets output formatted array
     *
     * @return array
     */
    private function getAttributeArray() : array
    {
        $attributeArray = [];
        /** @var \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute */
        foreach ($this->getAttributes() as $attribute) {
            if ($attribute->getFrontendInput()) {
                $attributeArray[$attribute->getFrontendInput()][] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getDefaultFrontendLabel(),
                    'attributeOptions' => $this->getOptions($attribute)
                ];
            }
        }
        return $attributeArray;
    }

    /**
     * Gets all product attributes
     *
     * @return array
     */
    private function getAttributes() : array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_visible', true)
            ->create();

        $attributeRepository = $this->attributeRepository->getList(
            \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
            $searchCriteria
        );

        return $attributeRepository->getItems();
    }

    /**
     * Get attribute options
     *
     * @param \Magento\Eav\Api\Data\AttributeInterface $attribute
     * @return array
     */
    private function getOptions(\Magento\Eav\Api\Data\AttributeInterface $attribute) : array
    {
        $return = [];

        $options = $attribute->getOptions();
        foreach ($options as $option) {
            if ($option->getValue()) {
                $return[] = [
                    'value' => $option->getLabel(),
                    'label' => $option->getLabel(),
                    'parentAttributeLabel' => $attribute->getDefaultFrontendLabel()
                ];
            }
        }

        return $return;
    }
}
