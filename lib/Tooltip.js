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

], function(Brickrouge) {

	var opened = []

	Brickrouge.Tooltip = new Class({

		Implements: [ Options ],

		options: {

			animation: true,
			placement: 'top',
			selector: false,
			template: '<div class="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>',
			trigger: 'hover',
			title: '',
			delay: 0,
			html: true

		},

		initialize: function(anchor, options)
		{
			this.setOptions(options)
			this.anchor = document.id(anchor)
			this.element = Elements.from(this.options.template).shift()
			this.setContent(anchor.get('data-tooltip-content'));
		},

		setContent: function(content)
		{
			this.element.querySelector('.tooltip-inner')[this.options.html ? 'innerHTML' : 'innerText'] = content

			;['fade', 'in', 'top', 'bottom', 'left', 'right'].forEach(this.element.classList.remove, this.element)
		},

		getPosition: function (inside)
		{
			var anchor = this.anchor
			, top = 0
			, left = 0
			, width = anchor.offsetWidth
			, height = anchor.offsetHeight

			if (!inside)
			{
				var position = anchor.getPosition()

				top = position.y
				left = position.x
			}

			// AREA position is inconsistent between IE and Firefox, thus we use the position of
			// the image using the MAP, then compute the location of the AREA using its
			// coordinates.

			if (anchor.tagName == 'AREA')
			{
				var x1 = null
				, x2 = null
				, y1 = null
				, y2 = null
				, map = anchor.parentNode
				, name = map.id || map.name
				, image = document.body.querySelector('[usemap="#' + name +'"]')

				position = image.getPosition()

				top = position.y
				left = position.x

				anchor.coords.match(/\d+\s*,\s*\d+/g).each(function(coords) {

					var xy = coords.match(/(\d+)\s*,\s*(\d+)/)
					, x = xy[1]
					, y = xy[2]

					x1 = (x1 === null) ? x : Math.min(x1, x)
					x2 = (x2 === null) ? x : Math.max(x2, x)
					y1 = (y1 === null) ? y : Math.min(y1, y)
					y2 = (y2 === null) ? y : Math.max(y2, y)
				})

				top += y1
				left += x1
				width = x2 - x1 + 1
				height = y2 - y1 + 1
			}

			return Object.append
			(
				{},
				{ y: top, x: left },
				{ width: width, height: height }
			)
		},

		show: function()
		{
			var el = this.element
			, options = this.options
			, placement = options.placement
			, inside
			, pos
			, actualWidth
			, actualHeight
			, tp = {}

			if (options.animation)
			{
				el.classList.add('fade')
			}

			if (typeOf(placement) == 'function')
			{
				placement = placement.call(this, el, anchor)
			}

			inside = /in/.test(placement)

			el.setStyles({ top: 0, left: 0, display: 'block' }).inject(inside ? this.anchor : document.body)

			actualWidth = el.offsetWidth
			actualHeight = el.offsetHeight

			pos = this.getPosition(inside)

			switch (inside ? placement.split(' ')[1] : placement)
			{
				case 'bottom':
					tp = {top: pos.y + pos.height, left: pos.x + pos.width / 2 - actualWidth / 2}
					break
				case 'top':
					tp = {top: pos.y - actualHeight, left: pos.x + pos.width / 2 - actualWidth / 2}
					break
				case 'left':
					tp = {top: pos.y + pos.height / 2 - actualHeight / 2, left: pos.x - actualWidth}
					break
				case 'right':
					tp = {top: pos.y + pos.height / 2 - actualHeight / 2, left: pos.x + pos.width}
					break
			}

			opened.unshift(this)

			el.setStyles(tp).classList.add(placement).classList.add('in')
		},

		hide: function()
		{
			var el = this.element

			opened.erase(this)

			el.classList.remove('in')
			el.dispose()
		}
	})

	Brickrouge.Tooltip.hideAll = function() {

		Array.slice(opened).each(function(tooltip) {

			tooltip.hide()

		})
	}

	document.body.addEvent('mouseenter:relay([data-tooltip-content])', function(ev, el) {

		var tooltip = el.retrieve('tooltip')

		if (!tooltip)
		{
			tooltip = new Brickrouge.Tooltip(el, Brickrouge.extractDataset(el, 'tooltip'))
			el.store('tooltip', tooltip)
		}

		tooltip.show()

	})

	document.body.addEvent('mouseleave:relay([data-tooltip-content])', function(ev, el) {

		try
		{
			el.retrieve('tooltip').hide()
		}
		catch (e) {}

	})

})
