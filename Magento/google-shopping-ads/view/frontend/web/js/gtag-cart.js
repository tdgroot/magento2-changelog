/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/*global gtag*/
define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'underscore',
    'jquery/ui'
], function ($, customerData, _) {
    'use strict';

    /**
     * Delete cookie
     *
     * @param {String} name
     */
    function delCookie(name) {
        var date = new Date(0);

        document.cookie = name + '=' + '; path=/; expires=' + date.toUTCString();
    }

    $.widget('mage.sccGtagCart', {
        options: {
            cookieAddToCart: '',
            cookieRemoveFromCart: ''
        },

        addedProducts: [],
        removedProducts: [],

        /**
         * Format products array
         *
         * @param {Object} productsIn
         *
         * @private
         */
        _formatProductsArray: function (productsIn) {
            var productsOut = [],
                itemId,
                i;

            /* eslint-disable max-depth */
            /* eslint-disable eqeqeq */
            for (i in productsIn) {

                if (i != 'length' && productsIn.hasOwnProperty(i)) {

                    if (!_.isUndefined(productsIn[i].sku)) {
                        itemId = productsIn[i].sku;
                    } else {
                        itemId = productsIn[i].id;
                    }

                    productsOut.push({
                        'id': itemId,
                        'name': productsIn[i].name,
                        'price': productsIn[i].price,
                        'quantity': parseInt(productsIn[i].qty, 10)
                    });
                }
            }

            /* eslint-enable max-depth */
            /* eslint-enable eqeqeq */

            return productsOut;
        },

        /**
         * Cart item add action
         *
         * @private
         */
        _cartItemAdded: function () {
            var products,
                params,
                i;

            if (!this.addedProducts.length) {
                return;
            }
            products = this._formatProductsArray(this.addedProducts);

            for (i = 0; i < products.length; i++) {
                params = {
                    'ecomm_prodid': products[i].id,
                    'ecomm_pagetype': 'cart'
                };
                gtag('event', 'add_to_cart', params);
            }

            this.addedProducts = [];
        },

        /**
         * Cart item remove action
         *
         * @private
         */
        _cartItemRemoved: function () {
            var products,
                params,
                i;

            if (!this.removedProducts.length) {
                return;
            }
            products = this._formatProductsArray(this.removedProducts);

            for (i = 0; i < products.length; i++) {
                params = {
                    'ecomm_prodid': products[i].id,
                    'ecomm_pagetype': 'cart'
                };
                gtag('event', 'remove_from_cart', params);
            }

            this.removedProducts = [];
        },

        /**
         * Parse add from cart cookies.
         *
         * @private
         */
        _parseAddToCartCookies: function () {
            var addProductsList;

            if ($.mage.cookies.get(this.options.cookieAddToCart)) {
                this.addedProducts = [];
                addProductsList = decodeURIComponent($.mage.cookies.get(this.options.cookieAddToCart));
                this.addedProducts = JSON.parse(addProductsList);
                delCookie(this.options.cookieAddToCart);
                this._cartItemAdded();
            }
        },

        /**
         * Parse remove from cart cookies.
         *
         * @private
         */
        _parseRemoveFromCartCookies: function () {
            var removeProductsList;

            if ($.mage.cookies.get(this.options.cookieRemoveFromCart)) {
                this.removedProducts = [];
                removeProductsList = decodeURIComponent($.mage.cookies.get(this.options.cookieRemoveFromCart));
                this.removedProducts = JSON.parse(removeProductsList);
                delCookie(this.options.cookieRemoveFromCart);
                this._cartItemRemoved();
            }
        },

        /**
         * @inheritdoc
         *
         * @private
         */
        _create: function () {
            var context = this;

            setInterval(function () {
                context._parseAddToCartCookies();
                context._parseRemoveFromCartCookies();
            }, 1000);
        }
    });

    return $.mage.sccGtagCart;
});
