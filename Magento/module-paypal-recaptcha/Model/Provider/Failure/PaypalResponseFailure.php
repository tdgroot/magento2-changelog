<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalReCaptcha\Model\Provider\Failure;

use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Json\EncoderInterface;
use MSP\ReCaptcha\Model\Config;
use MSP\ReCaptcha\Model\Provider\FailureProviderInterface;

/**
 * Handle reCaptcha failure for payment transparent redirect implementation.
 */
class PaypalResponseFailure implements FailureProviderInterface
{
    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ActionFlag $actionFlag
     * @param EncoderInterface $encoder
     * @param Config $config
     */
    public function __construct(
        ActionFlag $actionFlag,
        EncoderInterface $encoder,
        Config $config
    ) {
        $this->actionFlag = $actionFlag;
        $this->encoder = $encoder;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute(ResponseInterface $response = null)
    {
        $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);

        $jsonPayload = $this->encoder->encode([
            'success' => false,
            'error' => true,
            'error_messages' => $this->config->getErrorDescription(),
        ]);

        $response->representJson($jsonPayload);
    }
}
