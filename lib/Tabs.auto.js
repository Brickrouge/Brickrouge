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

],

/**
 * @param {Brickrouge} Brickrouge
 */
function (Brickrouge) {

	"use strict";

	/**
	 * Activates the pane associated with a tab.
	 */
	document.body.addDelegatedEventListener('[data-toggle="tab"]', 'click', (ev, el) => {

		let href = el.getAttribute('href')
		let pane
		let active

		if (el.classList.contains('disabled'))
		{
			ev.preventDefault()
			return
		}

		if (href == '#')
		{
			let index = Array.prototype.indexOf.call(el.closest('.nav-tabs').querySelectorAll('[data-toggle="tab"]'), el)

			pane = el.closest('.tabbable').querySelectorAll('.tab-content .tab-pane').item(index)
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

		active = el.closest('.nav-tabs').querySelector('.active')

		if (active)
		{
			active.classList.remove('active')
		}

		el.closest('.nav-link').classList.add('active')

		active = pane.closest('.tab-content').querySelector('.active')

		if (active)
		{
			active.classList.remove('active')
		}

		pane.classList.add('active')
	})

	/**
	 * Distributes the remaining space after the last tab between tabs.
	 *
	 * The distribution only applies to tabs which container have the `nav-tabs-fill` class.
	 */
	Brickrouge.observeUpdate(ev => {

		ev.fragment.querySelectorAll('.nav-tabs-fill').forEach(nav => {

			let tabs = nav.querySelectorAll('a')
			let last = tabs[tabs.length - 1]
			let lastCoordinates = last.getCoordinates(nav)
			let navSize = nav.getSize()
			let remain = (navSize.x - (lastCoordinates.left + lastCoordinates.width))
			let add = (remain / tabs.length / 2)|0
			let addFinal = remain - (add * tabs.length * 2)

			last.setStyle('padding-right', last.getStyle('padding-right').toInt() + addFinal)

			tabs.forEach(function(tab) {

				tab.setStyle('padding-left', tab.getStyle('padding-left').toInt() + add)
				tab.setStyle('padding-right', tab.getStyle('padding-right').toInt() + add)

			})
		})

	})

})
