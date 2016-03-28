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

], function (Brickrouge) {

	"use strict";

	/**
	 * Activates the pane associated with a tab.
	 */
	document.body.addDelegatedEventListener('[data-toggle="tab"]', 'click', function (ev, el) {

		var href = el.getAttribute('href')
		, pane
		, active

		if (el.classList.contains('disabled')) {
			ev.preventDefault()
			return
		}

		if (href == '#')
		{
			var index = Array.prototype.indexOf.call(el.closest('.nav-tabs').querySelectorAll('[data-toggle="tab"]'), el)

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
	Brickrouge.observe('update', function (fragment) {

		fragment.querySelectorAll('.nav-tabs-fill').forEach(function (nav) {

			var tabs = nav.querySelectorAll('a')
			, last = tabs[tabs.length - 1]
			, lastCoordinates = last.getCoordinates(nav)
			, navSize = nav.getSize()
			, remain = (navSize.x - (lastCoordinates.left + lastCoordinates.width))
			, add = (remain / tabs.length / 2)|0
			, addFinal = remain - (add * tabs.length * 2)

			last.setStyle('padding-right', last.getStyle('padding-right').toInt() + addFinal)

			tabs.forEach(function(tab) {

				tab.setStyle('padding-left', tab.getStyle('padding-left').toInt() + add)
				tab.setStyle('padding-right', tab.getStyle('padding-right').toInt() + add)

			})
		})

	})

})
