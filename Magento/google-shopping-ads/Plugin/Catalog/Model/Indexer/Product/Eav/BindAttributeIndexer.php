<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin\Catalog\Model\Indexer\Product\Eav;

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
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->feedIndexer = $feedIndexer;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Binding execute method
     *
     * @param \Magento\Framework\Indexer\ActionInterface $subject
     * @param array $ids
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecute(\Magento\Framework\Indexer\ActionInterface $subject, $ids)
    {
        if (!$this->indexerRegistry->get('scconnector_google_feed')->isScheduled()) {
            $this->feedIndexer->execute($ids);
        }
    }

    /**
     * Binding executeList method
     *
     * @param \Magento\Framework\Indexer\ActionInterface $subject
     * @param array $ids
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteList(\Magento\Framework\Indexer\ActionInterface $subject, array $ids)
    {
        if (!$this->indexerRegistry->get('scconnector_google_feed')->isScheduled()) {
            $this->feedIndexer->executeList($ids);
        }
    }

    /**
     * Binding executeRow method
     *
     * @param \Magento\Framework\Indexer\ActionInterface $subject
     * @param int $id
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeExecuteRow(\Magento\Framework\Indexer\ActionInterface $subject, $id)
    {
        if (!$this->indexerRegistry->get('scconnector_google_feed')->isScheduled()) {
            $this->feedIndexer->executeRow($id);
        }
    }
}
