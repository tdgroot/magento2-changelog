<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Controller responsible for invalidating product feed indexes
 */
class InvalidateIndexes extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Indexer\IndexerRegistry
     */
    private $indexerRegistry;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
    ) {
        parent::__construct($context);
        $this->indexerRegistry = $indexerRegistry;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->cacheTypeList->cleanType(\Magento\GoogleShoppingAds\Model\Cache\Type::TYPE_IDENTIFIER);

        $indexerIds = [
            'scconnector_google_feed',
            'scconnector_google_remove'
        ];
        foreach ($indexerIds as $indexerId) {
            $this->indexerRegistry->get($indexerId)->invalidate();
        }
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        return $result;
    }

    /**
     * Check is user can access to Google Advertising Channels Connector
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_GoogleShoppingAds::scconnector_google');
    }
}
