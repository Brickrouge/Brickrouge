/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function()
{
	var toggleSelector = '[data-toggle="dropdown"]'

	function clearMenus()
	{
		$$(toggleSelector).getParent().removeClass('open')
	}

	function toggle()
	{
		var selector = this.get('data-target') || this.get('href')
        , parent = document.id(selector) || this.getParent()
        , isActive

		isActive = parent.hasClass('open')

		clearMenus()

		!isActive && parent.toggleClass('open')

		return false
	}

	/**
	 * Clears all menus when the user clicks away
	 */
	window.addEvent('click', clearMenus)

	window.addEvent
	(
		'click:relay(' + toggleSelector + ')', function(ev, el)
		{
			ev.stop()
			toggle.bind(el)()
		}
	)
} ()
