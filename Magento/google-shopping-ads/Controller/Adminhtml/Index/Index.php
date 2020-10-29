<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleShoppingAds\Controller\Adminhtml\Index;

/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file.
 */
class Index extends \Magento\Backend\App\AbstractAction
{
    /**
     * @inheritdoc
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute() : \Magento\Framework\Controller\ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_GoogleShoppingAds::scconnector_google');
        $resultPage->getConfig()->getTitle()->prepend(__('Google Shopping ads'));
        $resultPage->setHeader(
            'Content-Security-Policy',
            "default-src 'self' data: 'unsafe-inline' "
                . "'unsafe-eval' channels.magento.com assets.adobedtm.com cdn.wootric.com d8myem934l1zi.cloudfront.net "
                . "wootric-eligibility.herokuapp.com amcglobal.sc.omtrdc.net stg-channels.magedevteam.com "
                . "dpm.demdex.net react-google-channel-qa.s3-website.us-east-2.amazonaws.com google-dev-bucket.s3.amazonaws.com "
                . "http://react-google-channel.s3-website.us-east-2.amazonaws.com eligibility.wootric.com s3.amazonaws.com/extension-release-notes/ "
                . "amc.demdex.net cm.everesttech.net production.wootric.com http://react-google-channel-qa.s3-website.us-east-2.amazonaws.com"
        );
        return $resultPage;
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
