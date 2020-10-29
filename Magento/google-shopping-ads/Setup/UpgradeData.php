<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\App\Cache\Manager as CacheManager;

/**
 * Class for module data upgrades
 */
class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var array
     */
    private $indexerIds;

    /**
     * @var CacheManager
     */
    private $cacheManager;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param array $indexerIds
     * @param CacheManager $cacheManager
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        array $indexerIds,
        CacheManager $cacheManager
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->indexerRegistry = $indexerRegistry;
        $this->indexerIds = $indexerIds;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @inheritdoc
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $setup->startSetup();
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'merchant_center_category',
                [
                    'wysiwyg_enabled'=> false,
                    'html_allowed_on_front'=> false,
                    'used_for_sort_by'=> false,
                    'filterable'=> false,
                    'filterable_in_search'=> false,
                    'used_in_grid'=> false,
                    'visible_in_grid'=> false,
                    'filterable_in_grid'=> false,
                    'position'=> 0,
                    'apply_to'=> 'simple,downloadable,virtual,bundle,configurable',
                    'searchable'=> false,
                    'visible_in_advanced_search'=> false,
                    'comparable'=> false,
                    'used_for_promo_rules'=> false,
                    'visible_on_front'=> false,
                    'used_in_product_listing'=> false,
                    'visible'=> false,
                    'scope'=> 'global',
                    'input'=> 'select',
                    'entity_type_id'=> 4,
                    'required'=> false,
                    'option'=> ['values' => GoogleCategories::GOOGLE_CATEGORIES],
                    'user_defined'=> false,
                    'label'=> 'Google Merchant Center Category',
                    'type'=> 'int',
                    'source'=> \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                    'unique'=> false,
                ]
            );
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            $setup->startSetup();
            $setup->updateTableRow(
                'core_config_data',
                'path',
                'scconnector/google/verification_configs',
                'path',
                'sales_channels/sales_channel_integration/verification_configs'
            );
            $setup->updateTableRow(
                'core_config_data',
                'path',
                'scconnector/google/gtag',
                'path',
                'sales_channels/sales_channel_integration/gtag'
            );
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            foreach ($this->indexerIds as $indexerId) {
                try {
                    $indexer = $this->indexerRegistry->get($indexerId);
                    $indexer->setScheduled(true);
                } catch (\Exception $e) {
                    //ignore if indexers were removed
                }
            }
        }

        if (version_compare($context->getVersion(), '1.2.0', '<')) {
            $setup->startSetup();
            /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $id = $eavSetup->getAttributeId(
                \Magento\Catalog\Model\Product::ENTITY,
                'merchant_center_category'
            );
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $id,
                'is_user_defined',
                true
            );
            $indexer = $this->indexerRegistry->get('scconnector_google_feed');
            $indexer->setScheduled(true);
            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '2.0.0', '>=')) {
            $setup->startSetup();
            $cacheType = ['google_product'];
            $enabledTypes = $this->cacheManager->setEnabled($cacheType, true);
            $this->cacheManager->clean($enabledTypes);
            $setup->endSetup();
        }
    }
}
