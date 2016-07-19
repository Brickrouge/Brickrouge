/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

const TOGGLE_SELECTOR = '[data-toggle="dropdown"]'
const TOGGLE_ENABLED_SELECTOR = TOGGLE_SELECTOR + ':not(.disabled)'

let skipEvent = null

function clearMenus()
{
	document.body.querySelectorAll(TOGGLE_SELECTOR).forEach(element => {

		element.parentNode.classList.remove('open')

	})
}

function toggle()
{
	const selector = this.getAttribute('data-target') || this.getAttribute('href')
	const parent = (selector ? document.getElementById(selector) : null) || this.parentNode
	const isActive = parent.classList.contains('open')

	clearMenus()

	if (!isActive) {
		parent.classList.toggle('open')
	}
}

document.body.addDelegatedEventListener(TOGGLE_ENABLED_SELECTOR, 'click', (ev, el) => {

	ev.preventDefault()
	ev.stopPropagation()

	skipEvent = ev
	toggle.apply(el)

})

/*
 * Clears all menus when the user clicks away.
 */
document.body.addEventListener('click', ev => {

	if (skipEvent === ev)
	{
		skipEvent = null
		return
	}

	clearMenus()

})
