/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

	'./Core',
	'./Viewport'

], function (Brickrouge) {
	"use strict";

	var popovers = []
	, viewport = Brickrouge.viewport

	function repositionPopovers()
	{
		popovers.forEach(function (popover) {

			if (!popover.isVisible() || !viewport.isElementVisible(popover.element)) {
				return
			}

			popover.reposition(true)

		})
	}

	window.addEventListener('load', repositionPopovers)
	window.addEventListener('resize', repositionPopovers)
	window.addEventListener('scroll', repositionPopovers)

	/**
	 * A subtextual information attached to an anchor.
	 *
	 * The popover element is a floating element attached to an anchor. It repositions itself to
	 * follow its anchor, and may change its placement according to the available space around it.
	 *
	 * @property element Element The popover element.
	 */
	Brickrouge.Popover = new Class({

		Implements: [Events, Options],

		options: {
			anchor: null,
			animate: false,
			popoverClass: null,
			placement: null,
			visible: false,
			fitContent: false,
			loveContent: false,
			iframe: null
		},

		initialize: function (el, options) {
			this.element = document.id(el)
			this.setOptions(options)
			this.arrow = this.element.getElement('.popover-arrow')
			this.actions = this.element.getElement('.popover-actions')
			this.repositionCallback = this.reposition.bind(this, false)
			this.quickRepositionCallback = this.reposition.bind(this, true)

			popovers.push(this)

			el = this.element
			options = this.options

			this.iframe = options.iframe

			if (options.anchor) {
				this.attachAnchor(options.anchor)
			}

			this.tween = null

			if (options.animate) {
				this.tween = new Fx.Tween(el, {
					property: 'opacity',
					link: 'cancel',
					duration: 'short'
				})
			}

			if (options.fitContent) {
				el.classList.add('fit-content')
			}

			if (options.popoverClass) {
				el.classList.add(options.popoverClass)
			}

			el.addDelegatedEventListener('.popover-actions [data-action]', 'click', function (ev, target) {

				this.fireAction({ action: target.get('data-action'), popover: this, event: ev })

			}.bind(this))

			if (options.visible) {
				this.show()
			}
		},

		fireAction: function (params) {
			this.fireEvent('action', arguments)
		},

		attachAnchor: function (anchor) {
			this.anchor = document.id(anchor)

			if (!this.anchor) {
				this.anchor = document.body.getElement(anchor)
			}

			this.reposition(true)
		},

		changePlacement: function (placement) {
			var placementClasses = {

				left: 'popover-left',
				right: 'popover-right',
				top: 'popover-top',
				bottom: 'popover-bottom'

			}

			var el = this.element

			Object.each(placementClasses, function (className) {

				el.classList.remove(className)

			})

			el.classList.add(placementClasses[placement])
		},

		show: function () {
			this.element.setStyles({ display: 'block', visibility: 'hidden' })

			if (this.iframe) {
				document.id(this.iframe.contentWindow).addEvents({

					load: this.quickRepositionCallback,
					resize: this.quickRepositionCallback,
					scroll: this.quickRepositionCallback

				})
			}

			document.body.appendChild(this.element)

			this.reposition(true)

			if (this.options.animate) {
				this.tween.set(0)
				this.element.setStyle('visibility', 'visible')
				this.tween.start(1)
			}
			else {
				this.element.setStyle('visibility', 'visible')
			}
		},

		hide: function () {
			var hide = function () {

				this.element.setStyle('display', '')
				this.element.remove()

			}.bind(this)

			if (this.iframe) {
				var contentWindow = document.id(this.iframe.contentWindow)

				contentWindow.removeEvent('load', this.quickRepositionCallback)
				contentWindow.removeEvent('resize', this.quickRepositionCallback)
				contentWindow.removeEvent('scroll', this.quickRepositionCallback)
			}

			if (this.options.animate) {
				this.tween.start(0).chain(hide)
			}
			else {
				hide()
			}
		},

		isVisible: function () {
			return this.element.parentNode &&
				this.element.getStyle('visibility') == 'visible' &&
				this.element.getStyle('display') != 'none'
		},

		computeAnchorBox: function () {
			var anchor = this.anchor
			, anchorCoords
			, iframe = this.iframe
			, iframeCoords
			, iHTML
			, visibleH
			, visibleW
			, hiddenTop
			, hiddenLeft
			, aX
			, aY
			, aW
			, aH

			if (iframe) {
				iframeCoords = iframe.getCoordinates()
				iHTML = iframe.contentDocument.documentElement

				aX = anchor.offsetLeft
				aY = anchor.offsetTop
				aW = anchor.offsetWidth
				aH = anchor.offsetHeight

				visibleH = iHTML.clientHeight
				hiddenTop = iHTML.scrollTop

				aY -= hiddenTop

				if (aY < 0) {
					aH += aY
				}

				aY = Math.max(aY, 0)
				aH = Math.min(aH, visibleH)

				visibleW = iHTML.clientWidth
				hiddenLeft = iHTML.scrollLeft

				aX -= hiddenLeft

				if (aX < 0) {
					aW += aX
				}

				aX = Math.max(aX, 0)
				aW = Math.min(aW, visibleW)

				aX += iframeCoords.left
				aY += iframeCoords.top
			}
			else {
				anchorCoords = anchor.getCoordinates()

				aX = anchorCoords.left
				aY = anchorCoords.top
				aH = anchorCoords.height
				aW = anchorCoords.width
			}

			return { x: aX, y: aY, w: aW, h: aH }
		},

		/**
		 * Compute best placement of the popover relative to its anchor.
		 *
		 * If the placement is defined in the option it is used unless there is not enough available
		 * space for the popover.
		 *
		 * Emplacements are tried in the following order: 'right', 'left', 'top' and fallback to
		 * 'bottom'.
		 */
		computeBestPlacement: function (anchorBox, w, h) {
			var html = document.body.parentNode
			, bodyCompleteW = html.scrollWidth
			, aX = anchorBox.x
			, aY = anchorBox.y
			, aW = anchorBox.w
			, placement
			, pad = 20

			function fitBefore() {
				return aX + 1 > w + pad * 2
			}

			function fitAfter() {
				return bodyCompleteW - (aX + 1 + aW) > w + pad * 2
			}

			function fitAbove() {
				return aY + 1 > h + pad * 2
			}

			placement = this.options.placement

			switch (placement) {
				case 'right':
					if (fitAfter()) return placement;
					break
				case 'left':
					if (fitBefore()) return placement;
					break
				case 'top':
					if (fitAbove()) return placement;
					break
				case 'bottom':
					return placement
			}

			if (fitAfter()) return 'right'
			if (fitBefore()) return 'left'
			if (fitAbove()) return 'top'

			return 'bottom'
		},

		reposition: function (quick) {
			if (!this.anchor) {
				return
			}

			if (quick === undefined) {
				quick = this.element.getStyle('visibility') != 'visible'
			}

			var pad = 20, actions = this.actions
			, anchorBox, aX, aY, aW, aH, anchorMiddleX, anchorMiddleY
			, size = this.element.getSize(), w = size.x, h = size.y, x, y
			, placement
			, vpCoordinates = viewport.getCoordinates()
			, bodyX = vpCoordinates.left
			, bodyY = vpCoordinates.top
			, bodyW = vpCoordinates.width
			, bodyH = vpCoordinates.height
			, arrowTransform = { top: null, left: null }, arX, arY

			anchorBox = this.computeAnchorBox()
			aX = anchorBox.x
			aY = anchorBox.y
			aW = anchorBox.w
			aH = anchorBox.h
			anchorMiddleX = aX + aW / 2 - 1
			anchorMiddleY = aY + aH / 2 - 1

			placement = this.computeBestPlacement(anchorBox, w, h)

			this.changePlacement(placement)

			if (placement == 'left' || placement == 'right') {
				y = Math.round(aY + (aH - h) / 2 - 1)
				x = (placement == 'left') ? aX - w + 1 : aX + aW - 1

				//
				// limit 'x' and 'y' to the limits of the document incuding a padding value.
				//

				x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
				y = y.limit(bodyY + pad - 1, bodyY + bodyH - (h + pad) - 1)
			}
			else {
				x = Math.round(aX + (aW - w) / 2 - 1)
				y = (placement == 'top') ? aY - h + 1 : aY + aH - 1

				//
				// limit 'x' and 'y' to the limits of the document incuding a padding value.
				//

				x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
				//y = y.limit(bodyY + pad, bodyY + bodyH - (h + pad))
			}

			// adjust arrow

			if (h > pad * 2) {
				if (placement == 'left' || placement == 'right') {
					arY = (aY + aH / 2 - 1) - y

					arY = Math.min(h - (actions ? actions.getSize().y : pad) - 10, arY)
					arY = Math.max(pad, arY)

					// adjust element Y so that the arrow is always centered on the anchor visible height

					if (arY + y - 1 != anchorMiddleY) {
						y -= (y + arY) - anchorMiddleY
					}

					arrowTransform.top = arY
				}
				else {
					arX = ((aX + aW / 2 - 1) - x).limit(pad, w - pad)

					// adjust element X so that the arrow is always centered on the anchor visible width

					if (arX + w - 1 != anchorMiddleX) {
						x -= (x + arX) - anchorMiddleX
					}

					arrowTransform.left = arX
				}
			}

			x = Math.floor(Math.max(50, x))
			y = Math.floor(Math.max(50, y))

			if (quick) {
				this.element.setStyles({ left: x, top: y })
				this.arrow.setStyles(arrowTransform)
			}
			else {
				this.element.morph({ left: x, top: y })
				this.arrow.morph(arrowTransform)
			}
		}
	})

	/**
	 * Creates a popover element using the provided options.
	 *
	 * @param options
	 *
	 * @return Brickrouge.Popover
	 */
	Brickrouge.Popover.from = function (options) {
		var popover
		, title = options.title
		, content = options.content
		, actions = options.actions
		, inner = new Element('div.popover-inner')

		if (title) {
			inner.adopt(new Element('h3.popover-title', { 'html': title }))
		}

		if (typeOf(content) == 'string') {
			inner.adopt(new Element('div.popover-content', { 'html': content }))
		}
		else {
			inner.adopt(new Element('div.popover-content').adopt(content))

			if (options.fitContent === undefined) {
				options.fitContent = true
			}
		}

		if (actions == 'boolean') {
			actions = [
				new Element('button.btn.btn-secondary.btn-cancel[data-action="cancel"]', {
					html: Locale.get('Popover.cancel') || 'Cancel'
				}),

				new Element('button.btn.btn-primary[data-action="ok"]', {
					html: Locale.get('Popover.ok') || 'Ok'
				})
			]
		}

		if (actions) {
			inner.adopt(new Element('div.popover-actions').adopt(actions))
		}

		popover = new Element('div.popover').adopt([new Element('div.popover-arrow'), inner])

		return new Brickrouge.Popover(popover, options)
	}

	/**
	 * Popover widget constructor.
	 */
	Brickrouge.register('Popover', function (element, options) {

		return new Brickrouge.Popover(element, options)

	})

	return Brickrouge.Popover

})
