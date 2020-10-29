<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Controller responsible for dealing with the requests from the react app.
 */
class Middleware extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\GoogleShoppingAds\Model\ServiceClient
     */
    private $serviceClient;

    /**
     * @inheritdoc
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\GoogleShoppingAds\Model\ServiceClient $serviceClient,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->serviceClient = $serviceClient;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function execute()
    {
        $jsonResult = $this->resultJsonFactory->create();

        $payload = $this->getRequest()->getParam('payload', '');
        $method = $this->getRequest()->getParam('method', 'get');
        $uri = $this->getRequest()->getParam('uri', 'channels');
        $result = $this->serviceClient->request($method, $uri, $payload);

        $jsonResult->setData($result);
        return $jsonResult;
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
