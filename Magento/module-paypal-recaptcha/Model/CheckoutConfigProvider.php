<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PaypalReCaptcha\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use MSP\ReCaptcha\Model\LayoutSettings;

/**
 * Adds reCaptcha configuration to checkout.
 */
class CheckoutConfigProvider implements ConfigProviderInterface
{
    /**
     * @var LayoutSettings
     */
    private $layoutSettings;

    /**
     * @param LayoutSettings $layoutSettings
     */
    public function __construct(
        LayoutSettings $layoutSettings
    ) {
        $this->layoutSettings = $layoutSettings;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        return [
            'msp_recaptcha' => $this->layoutSettings->getCaptchaSettings()
        ];
    }
}
