/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

	'./Core'

], (Brickrouge) => {

	"use strict";

	const SearchBox = new Class({

		Implements: Brickrouge.Utils.Busy,

		initialize: function(el, options) {

			this.element = el
			this.options = options

		}

	})

	Brickrouge.register('SearchBox', (element, options) => {

		return new SearchBox(element, options)

	})

})
