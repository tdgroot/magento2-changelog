<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Controller responsible for getting product edit urls
 */
class ProductEditUrl extends \Magento\Backend\App\AbstractAction
{
    /**
     * Key parameter name
     */
    const PARAMETER_KEY = 'skus';

    /**
     * @var \Magento\GoogleShoppingAds\Model\ProductEditUrlRetriever
     */
    private $productEditUrlRetriever;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\GoogleShoppingAds\Model\ProductEditUrlRetriever $productEditUrlRetriever
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\GoogleShoppingAds\Model\ProductEditUrlRetriever $productEditUrlRetriever
    ) {
        parent::__construct($context);
        $this->productEditUrlRetriever = $productEditUrlRetriever;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $skus = $this->getRequest()->getParam(self::PARAMETER_KEY, '[]');
        $urls = $this->productEditUrlRetriever->getProductEditUrls(json_decode($skus, true));
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        $result->setData($urls);
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
