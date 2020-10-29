<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Controller responsible for creating attributes upon requests from the react app.
 */
class CreateAttribute extends \Magento\Backend\App\AbstractAction
{
    /**
     * @var \Magento\Framework\Webapi\ServiceInputProcessor
     */
    private $serviceInputProcessor;

    /**
     * @var \Magento\GoogleShoppingAds\Model\AttributeCreator
     */
    private $attributeCreator;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Webapi\ServiceInputProcessor $serviceInputProcessor
     * @param \Magento\GoogleShoppingAds\Model\AttributeCreator $attributeCreator
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Webapi\ServiceInputProcessor $serviceInputProcessor,
        \Magento\GoogleShoppingAds\Model\AttributeCreator $attributeCreator
    ) {
        parent::__construct($context);
        $this->serviceInputProcessor = $serviceInputProcessor;
        $this->attributeCreator = $attributeCreator;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON);
        try {
            $payload = $this->getRequest()->getParam('attributeData');
            $inputData = json_decode($payload, true);
            $inputParams = $this->serviceInputProcessor->process(
                \Magento\Catalog\Api\ProductAttributeRepositoryInterface::class,
                'save',
                $inputData
            );
            $code = $this->attributeCreator->create($inputParams[0]);
            $result->setData(['attributeCode' => $code]);
        } catch (\Exception $e) {
            $result->setHttpResponseCode(\Magento\Framework\Webapi\ErrorProcessor::DEFAULT_ERROR_HTTP_CODE);
            $result->setData(
                ['error' => $e->getMessage()]
            );
        }
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
