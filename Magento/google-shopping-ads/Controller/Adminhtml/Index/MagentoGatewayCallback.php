<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Magento\Backend\App\AbstractAction;

/**
 * Controller responsible for dealing with the response from the magento.com gateway.
 */
class MagentoGatewayCallback extends AbstractAction
{
    /**
     * Key parameter name
     */
    const PARAMETER_KEY = 'key';

    /**
     * Path to magento key setting
     */
    const PATH_MAGENTO_KEY = 'sales_channels/sales_channel_integration/api_key';

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writer;

    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $reinitableConfig;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer,
        \Magento\Framework\App\Config\ReinitableConfigInterface $reinitableConfig
    ) {
        $this->writer = $writer;
        $this->reinitableConfig = $reinitableConfig;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $key = $this->getRequest()->getPostValue(self::PARAMETER_KEY);
        if (!$key) {
            throw new ParameterNotFoundException(self::PARAMETER_KEY);
        }
        $this->writer->save(self::PATH_MAGENTO_KEY, $key);
        $this->reinitableConfig->reinit();
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $result->setContents($key);
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
