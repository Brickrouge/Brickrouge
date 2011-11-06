/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

BrickRouge.Widget.Searchbox = new Class({

	Implements: BrickRouge.Utils.Busy,

	initialize: function(el, options)
	{
		this.element = document.id(el);
	}
});
