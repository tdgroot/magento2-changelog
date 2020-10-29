<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin\Catalog\Controller\Adminhtml\Product\MassStatus;

/**
 * Binds our remove indexer calls to the mass delete controller
 */
class BindIndexers
{
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer
     */
    private $removeIndexer;
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer
     */
    private $feedIndexer;
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer
     * @param \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer,
        \Magento\GoogleShoppingAds\Model\Indexer\FeedIndexer $feedIndexer,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->removeIndexer = $removeIndexer;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->feedIndexer = $feedIndexer;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute remove indexation process for deleted products
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\MassStatus $subject
     * @param callable $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Product\MassStatus $subject, callable $proceed)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ids = $collection->getAllIds();
        $result = $proceed();
        $this->feedIndexer->execute($ids);
        return $result;
    }
}
