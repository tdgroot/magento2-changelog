<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Controller responsible for dealing with the response from the google gateway.
 */
class GoogleCallback extends \Magento\Backend\App\AbstractAction
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $email = $this->getRequest()->getPostValue('email');
        $result = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $result->setContents($email);
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
