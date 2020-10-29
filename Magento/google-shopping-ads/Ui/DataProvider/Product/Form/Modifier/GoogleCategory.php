<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShoppingAds\Ui\DataProvider\Product\Form\Modifier;

/**
 * Class for the Edit Product form meta modifying
 */
class GoogleCategory implements \Magento\Ui\DataProvider\Modifier\ModifierInterface
{

    /**
     * Add the Google Merchant Center category attribute to Add/Edit product page
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if (isset(
            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']
        )) {
            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['filterOptions'] = true;

            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['multiple'] = false;

            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['elementTmpl'] = 'ui/grid/filters/elements/ui-select';

            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['component'] = 'Magento_Ui/js/form/element/ui-select';

            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['levelsVisibility'] = 0;

            $options = $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['options'];

            $categorizedOptions = [];
            foreach ($options as $option) {
                $parts = explode('>', $option['label']);
                $firstPart = trim($parts[0]);
                if (!isset($categorizedOptions[$firstPart])) {
                    $categorizedOptions[$firstPart] = [
                        'label' => $firstPart,
                        'is_active' => 1,
                        'optgroup' => []
                    ];
                }
                if (count($parts) == 1) {
                    $categorizedOptions[$firstPart]['value'] = $option['value'];
                }
                $categorizedOptions[$firstPart]['optgroup'][] = $option;
            }

            $meta['product-details']['children']['container_merchant_center_category']['children']
            ['merchant_center_category']['arguments']['data']['config']
            ['options'] = array_values($categorizedOptions);
        }
        return $meta;
    }

    /**
     * Modify Add/Edit Product meta data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }
}
