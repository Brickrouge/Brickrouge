/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var toggleSelector = '[data-toggle="dropdown"]'
, skipEvent = null

function clearMenus()
{
	Array.prototype.forEach.call(document.body.querySelectorAll(toggleSelector), function (element) {

		element.parentNode.classList.remove('open')

	})
}

function toggle()
{
	var selector = this.getAttribute('data-target') || this.getAttribute('href')
	, parent = (selector ? document.getElementById(selector) : null) || this.parentNode
	, isActive

	isActive = parent.classList.contains('open')

	clearMenus()

	!isActive && parent.classList.toggle('open')
}

document.body.addDelegatedEventListener(toggleSelector + ':not(.disabled)', 'click', function (ev, el) {

	ev.preventDefault()
	ev.stopPropagation()

	skipEvent = ev
	toggle.apply(el)

})

/*
 * Clears all menus when the user clicks away.
 */
document.body.addEventListener('click', function(ev) {

	if (skipEvent === ev)
	{
		skipEvent = null
		return
	}

	clearMenus()

})
