<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Plugin\Catalog\Controller\Adminhtml\Product\MassDelete;

/**
 * Binds our remove indexer calls to the mass delete controller
 */
class BindRemoveIndexer
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
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @param \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     */
    public function __construct(
        \Magento\GoogleShoppingAds\Model\Indexer\RemoveIndexer $removeIndexer,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
    ) {
        $this->removeIndexer = $removeIndexer;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * Execute remove indexation process for deleted products
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\MassDelete $subject
     * @param callable $proceed
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(\Magento\Catalog\Controller\Adminhtml\Product\MassDelete $subject, callable $proceed)
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $ids = $collection->getAllIds();
        $result = $proceed();
        if (!$this->indexerRegistry->get('scconnector_google_remove')->isScheduled()) {
            $this->removeIndexer->execute($ids);
        }
        return $result;
    }
}
