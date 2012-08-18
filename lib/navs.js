/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Activates the pane associated with a tab.
 */
document.body.addEvent('click:relay(.tabbable .nav-tabs a)', function(ev, el) {

	var href = el.get('href')
	, pane
	, active

	if (href == '#')
	{
		var index = el.getParent('.nav-tabs').getElements('a').indexOf(el)

		pane = el.getParent('.tabbable').getElement('.tab-content').getChildren()[index]
	}
	else
	{
		pane = document.id(href.substring(1))
	}

	ev.preventDefault()

	if (!pane)
	{
		throw new Error('Invalid pane id: ' + href)
	}

	active = el.getParent('.nav-tabs').getFirst('.active')

	if (active)
	{
		active.removeClass('active')
	}

	el.getParent('li').addClass('active')

	active = pane.getParent('.tab-content').getFirst('.active')

	if (active)
	{
		active.removeClass('active')
	}

	pane.addClass('active')
})
