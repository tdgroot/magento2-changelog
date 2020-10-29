<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GoogleShoppingAds\ViewModel;

/**
 * JS escaper for arrays and values
 */
class JsEscaper implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Escape JS in string value
     *
     * @param string $string
     * @return string
     */
    public function escapeValue($string) : string
    {
        if (strlen($string) && !ctype_digit($string)) {
            $string =  preg_replace_callback(
                '/[\'\/"]/Su',
                function ($matches) {
                    $chr = $matches[0];
                    if (strlen($chr) !== 1) {
                        $chr = mb_convert_encoding($chr, 'UTF-16BE', 'UTF-8');
                        $chr = ($chr === false) ? '' : $chr;
                    }
                    return sprintf('\\u%04s', strtoupper(bin2hex($chr)));
                },
                $string
            );
        }

        return $string;
    }
}
