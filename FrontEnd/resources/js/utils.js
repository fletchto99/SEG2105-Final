/**
 *  Author(s)           :  Matt Langlois, Kurt Bruneau
 *  File Created        :  August 2015
 *  File updated        :  December 2015
 *  Details             :  This file contains all of the application's Helper functions
 */

(function () {
    'use strict';

    /**
     * Injects an array object into an array of objects
     *
     * @param idx The index to inject into the parent array
     * @param {Array} arr The array to inject
     */
    Array.prototype.injectArray = function (idx, arr) {
        this.splice.apply(this, [idx, 0].concat(arr));
    };


    /**
     * Helper function acting as a shortcut for document.createElement()
     *
     * @param {Array} params The parameters used to build the element
     * @returns {Element} The created element
     */
    window.createElement = function (params) {
        if (!params) {
            throw Error('No parameters passed to create element');
        }

        if (params instanceof HTMLElement) {
            return params;
        }

        if (typeof params === 'string') {
            return document.createElement(params);
        }

        var elem = document.createElement(params.elem || 'span');
        params = cloneBasicObject(params);
        delete params.elem;

        // Put this element into a parent
        if (params.putIn) {
            params.putIn.appendChild(elem);
            delete params.putIn;
        }

        // Get array of elements to put into this
        var toGoInside = [];
        if (params.inside) {
            toGoInside = params.inside;
            delete params.inside;
        }

        // Apply HTML attributes
        for (var key in params.attributes) {
            elem.setAttribute(key, params.attributes[key]);
        }
        delete params.attributes;

        // Apply javascript attributes
        for (key in params) {
            elem[key] = params[key];
        }

        // Iterate over putIn array and put them into elem
        for (var i = 0, l = toGoInside.length; i < l; i++) {
            var e = toGoInside[i];
            if (!(e instanceof Element)) {
                e = createElement(e);
            }

            elem.appendChild(e);
        }

        return elem;
    };

    /**
     * Creates a clone of a basic object.
     *
     * @param {Object} obj The object to be cloned
     * @returns {Object} The clone of the original object
     */
    window.cloneBasicObject = function (obj) {
        if (null === obj || "object" != typeof obj) {
            return obj;
        }

        var copy;

        // Handle Date
        if (obj instanceof Date) {
            copy = new Date();
            copy.setTime(obj.getTime());
            return copy;
        }

        // Handle Array
        if (obj instanceof Array) {
            copy = obj.slice();
            for (var i = 0; i < copy.length; i++) {
                copy[i] = cloneBasicObject(obj[i]);
            }
            return copy;
        }

        // Handle Object
        if (obj instanceof Object) {
            copy = {};
            for (var attr in obj) {
                if (obj.hasOwnProperty(attr)) {
                    copy[attr] = obj[attr];
                }
            }
            return copy;
        }
    };

})();