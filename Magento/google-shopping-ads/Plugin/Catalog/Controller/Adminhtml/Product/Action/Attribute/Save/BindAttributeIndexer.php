<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;

/**
 * Binds our attribute indexer calls to the appropriate EAV indexer ones
 */
class BindAttributeIndexer
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer
     */
    private $feedIndexer;

    /**
     * @var \Magento\Catalog\Helper\Product\Edit\Action\Attribute
     */
    private $attribute;

    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer
     */
    private $removeIndexer;

    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer
     * @param \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer
     * @param \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attribute
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer,
        \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer,
        \Magento\Catalog\Helper\Product\Edit\Action\Attribute $attribute,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->feedIndexer = $feedIndexer;
        $this->attribute = $attribute;
        $this->removeIndexer = $removeIndexer;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute attributes indexation process for edited products
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject
     * @param \Magento\Backend\Model\View\Result\Redirect $result
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(\Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save $subject, $result)
    {
        $ids = $this->attribute->getProductIds();
        if (!$this->indexerRegistry->get('scconnector_google_remove')->isScheduled()) {
            $this->removeIndexer->execute($ids);
        }
        if (!$this->indexerRegistry->get('scconnector_google_feed')->isScheduled()) {
            $this->feedIndexer->execute($ids);
        }
        return $result;
    }
}
