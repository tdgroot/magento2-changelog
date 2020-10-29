/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global gtag*/
define([
    'jquery',
    'underscore'
], function ($, _) {

    $.widget('mage.sccGtagConfigurable', {
        options: {
            skuToOptions: {},
            category: ""
        },

        /**
         * Get currently selected options
         *
         * @private
         */
        _getSelectedOptionsArray: function () {
            var optionsArray = {};
            $('.super-attribute-select').each(function(key, element){
                var optionVal = element.value;
                var optionKey = element.name.replace(/\D/g, '');
                optionsArray[optionKey] = optionVal;
            });
            return optionsArray;
         },

        /**
         * @inheritdoc
         *
         * @private
         */
        _create: function () {
            var context = this;
            $('body').on('change', 'input.super-attribute-select', function () {
                var selectedOptions = context._getSelectedOptionsArray();
                _.each(context.options.skuToOptions, function (options, sku) {
                    if (_.isMatch(options, selectedOptions)) {
                        gtag('event', 'view_item', {
                            'ecomm_prodid': sku,
                            'ecomm_pagetype': 'product',
                            'ecomm_category': context.options.category
                        });
                    }
                });
            });
        }
    });

    return $.mage.sccGtagConfigurable;
});
