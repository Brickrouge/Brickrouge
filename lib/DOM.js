/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

], function () {

	/**
	 * The `forEach()` method executes a provided function once per object property.
	 *
	 * @param {object} object
	 * @param {function} callback
	 * @param {object} [thisArg]
	 */
	Object.forEach = Object.forEach || function(object, callback, thisArg) {

		for (let k in object)
		{
			if (!object.hasOwnProperty(k)) continue

			callback.call(thisArg, object[k], k, object)
		}

	}

	/*
	 * Element.prototype
	 */
	!function (prototype) {

		/**
		 * Returns the children of this element.
		 *
		 * @param {string|null} selector An optional selector.
		 *
		 * @returns {Array}
		 */
		prototype.getChildren = prototype.getChildren || function (selector) {

			"use strict";

			var children = []
			, child = this.firstElementChild

			while (child)
			{
				if (!selector || selector && child.matches(selector)) {
					children.push(child)
				}

				child = child.nextElementSibling
			}

			return children

		}

		/**
		 * Adds a delegated event listener.
		 *
		 * @param {string} selector
		 * @param {string} type
		 * @param {function} listener
		 * @param {boolean} [useCapture]
		 */
		prototype.addDelegatedEventListener = function(selector, type, listener, useCapture) {

			this.addEventListener(type, function(ev) {

				var target = ev.target
				, delegationTarget = target.closest(selector)

				if (!delegationTarget) {
					return null
				}

				listener(ev, delegationTarget, target)

			}, useCapture)

		}

	} (Element.prototype)

	/*
	 * NotList.prototype
	 */
	!function (prototype) {

		/**
		 * The `forEach()` method executes a provided function once per node.
		 *
		 * @param {function} callback
		 * @param {object} [thisArg]
		 */
		prototype.forEach = prototype.forEach || function(callback, thisArg) {

			Array.prototype.forEach.call(this, callback, thisArg)

		}

	} (NodeList.prototype)

})
