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
	'./Viewport',
	'olvlvl-subject'

],

/**
 * @param {Brickrouge} Brickrouge
 * @param {Brickrouge.Viewport} Viewport
 * @param {Brickrouge.Subject} Subject
 *
 * @return {Brickrouge.Popover}
 */
(Brickrouge, Viewport, Subject) => {

	"use strict";

	/**
	 * @param {string} action
	 * @param {object} popover
	 * @param {Event} originalEvent
	 *
	 * @event Brickrouge.Popover#action
	 * @property {string} action
	 * @property {object} popover
	 * @property {Event} originalEvent
	 */
	const ActionEvent = Subject.createEvent(function (action, popover, originalEvent) {

		this.action = action
		this.popover = popover
		this.event = originalEvent

	})

	/**
	 * @type {Brickrouge.Popover.Options|*}
	 */
	const DEFAULT_OPTIONS = {

		anchor: null,
		animate: false,
		popoverClass: null,
		placement: null,
		visible: false,
		fitContent: false,
		loveContent: false,
		iframe: null

	}

	const popovers = []
	const viewport = new Viewport

	/**
	 * A subtextual information attached to an anchor.
	 *
	 * The popover element is a floating element attached to an anchor. It repositions itself to
	 * follow its anchor, and may change its placement according to the available space around it.
	 *
	 * @property {Element} Element The popover element.
	 * @property {DEFAULT_OPTIONS} options
	 */
	class Popover {

		/**
		 * @returns {Brickrouge.Popover.Options}
		 * @constructor
		 */
		static get DEFAULT_OPTIONS()
		{
			return DEFAULT_OPTIONS
		}

		/**
		 * @return {Brickrouge.Popover.ActionEvent}
		 * @constructor
		 */
		static get ActionEvent()
		{
			return ActionEvent
		}

		/**
		 * Creates a popover element using the provided options.
		 *
		 * @param {object} options
		 *
		 * @return {Popover}
		 */
		static from(options)
		{
			let popover
			let title = options.title
			let content = options.content
			let actions = options.actions
			let inner = new Element('div.popover-inner')

			if (title)
			{
				inner.adopt(new Element('h3.popover-title', { 'html': title }))
			}

			if ('string' == typeOf(content))
			{
				inner.adopt(new Element('div.popover-content', { 'html': content }))
			}
			else
			{
				inner.adopt(new Element('div.popover-content').adopt(content))

				if (options.fitContent === undefined)
				{
					options.fitContent = true
				}
			}

			if ('boolean' == actions)
			{
				actions = [
					new Element('button.btn.btn-secondary.btn-cancel[data-action="cancel"]', {
						html: Locale.get('Popover.cancel') || 'Cancel'
					}),

					new Element('button.btn.btn-primary[data-action="ok"]', {
						html: Locale.get('Popover.ok') || 'Ok'
					})
				]
			}

			if (actions)
			{
				inner.adopt(new Element('div.popover-actions').adopt(actions))
			}

			popover = new Element('div.popover').adopt([new Element('div.popover-arrow'), inner])

			return new Popover(popover, options)
		}

		/**
		 * @param {Element} el
		 * @param {DEFAULT_OPTIONS} options
		 */
		constructor(el, options)
		{
			this.element = el
			this.options = options = Object.assign({}, DEFAULT_OPTIONS, options)

			this.arrow = this.element.querySelector('.popover-arrow')
			this.actions = this.element.querySelector('.popover-actions')
			this.repositionCallback = this.reposition.bind(this, false)
			this.quickRepositionCallback = this.reposition.bind(this, true)

			popovers.push(this)

			this.iframe = options.iframe

			if (options.anchor)
			{
				this.attachAnchor(options.anchor)
			}

			this.tween = null

			if (options.animate)
			{
				this.tween = new Fx.Tween(el, {
					property: 'opacity',
					link: 'cancel',
					duration: 'short'
				})
			}

			if (options.fitContent)
			{
				el.classList.add('fit-content')
			}

			if (options.popoverClass)
			{
				el.classList.add(options.popoverClass)
			}

			el.addDelegatedEventListener('.popover-actions [data-action]', 'click', (ev, target) => {

				this.notify(new ActionEvent(target.getAttribute('data-action'), this, ev))

			})

			if (options.visible)
			{
				this.show()
			}
		}

		/**
		 * @param {Element|string} anchor
		 */
		attachAnchor(anchor)
		{
			if (typeof anchor === 'string')
			{
				this.anchor = document.body.querySelector(anchor)
			}
			else if (anchor instanceof Element)
			{
				this.anchor = anchor
			}
			else
			{
				throw new Error("Anchor must be an element or a selector")
			}

			this.reposition(true)
		}

		/**
		 * @param {string} placement
		 */
		changePlacement(placement)
		{
			const placementClasses = {

				left: 'popover-left',
				right: 'popover-right',
				top: 'popover-top',
				bottom: 'popover-bottom'

			}

			let el = this.element

			Object.forEach(placementClasses, className => {

				el.classList.remove(className)

			})

			el.classList.add(placementClasses[placement])
		}

		show()
		{
			this.element.setStyles({ display: 'block', visibility: 'hidden' })

			if (this.iframe)
			{
				document.id(this.iframe.contentWindow).addEvents({

					load: this.quickRepositionCallback,
					resize: this.quickRepositionCallback,
					scroll: this.quickRepositionCallback

				})
			}

			document.body.appendChild(this.element)

			this.reposition(true)

			if (this.options.animate)
			{
				this.tween.set(0)
				this.element.setStyle('visibility', 'visible')
				this.tween.start(1)
			}
			else
			{
				this.element.setStyle('visibility', 'visible')
			}
		}

		hide()
		{
			const hide = () => {

				this.element.setStyle('display', '')
				this.element.remove()

			}

			if (this.iframe)
			{
				const contentWindow = document.id(this.iframe.contentWindow)

				contentWindow.removeEvent('load', this.quickRepositionCallback)
				contentWindow.removeEvent('resize', this.quickRepositionCallback)
				contentWindow.removeEvent('scroll', this.quickRepositionCallback)
			}

			if (this.options.animate)
			{
				this.tween.start(0).chain(hide)
			}
			else
			{
				hide()
			}
		}

		isVisible()
		{
			return this.element.parentNode &&
				this.element.getStyle('visibility') == 'visible' &&
				this.element.getStyle('display') != 'none'
		}

		computeAnchorBox()
		{
			let anchor = this.anchor
			let anchorCoords
			let iframe = this.iframe
			let iframeCoords
			let iHTML
			let visibleH
			let visibleW
			let hiddenTop
			let hiddenLeft
			let aX
			let aY
			let aW
			let aH

			if (iframe)
			{
				iframeCoords = iframe.getCoordinates()
				iHTML = iframe.contentDocument.documentElement

				aX = anchor.offsetLeft
				aY = anchor.offsetTop
				aW = anchor.offsetWidth
				aH = anchor.offsetHeight

				visibleH = iHTML.clientHeight
				hiddenTop = iHTML.scrollTop

				aY -= hiddenTop

				if (aY < 0)
				{
					aH += aY
				}

				aY = Math.max(aY, 0)
				aH = Math.min(aH, visibleH)

				visibleW = iHTML.clientWidth
				hiddenLeft = iHTML.scrollLeft

				aX -= hiddenLeft

				if (aX < 0)
				{
					aW += aX
				}

				aX = Math.max(aX, 0)
				aW = Math.min(aW, visibleW)

				aX += iframeCoords.left
				aY += iframeCoords.top
			}
			else
			{
				anchorCoords = anchor.getCoordinates()

				aX = anchorCoords.left
				aY = anchorCoords.top
				aH = anchorCoords.height
				aW = anchorCoords.width
			}

			return { x: aX, y: aY, w: aW, h: aH }
		}

		/**
		 * Compute best placement of the popover relative to its anchor.
		 *
		 * If the placement is defined in the option it is used unless there is not enough available
		 * space for the popover.
		 *
		 * Emplacements are tried in the following order: 'right', 'left', 'top' and fallback to
		 * 'bottom'.
		 */
		computeBestPlacement(anchorBox, w, h)
		{
			let html = document.body.parentNode
			let bodyCompleteW = html.scrollWidth
			let aX = anchorBox.x
			let aY = anchorBox.y
			let aW = anchorBox.w
			let placement
			let pad = 20

			function fitBefore()
			{
				return aX + 1 > w + pad * 2
			}

			function fitAfter()
			{
				return bodyCompleteW - (aX + 1 + aW) > w + pad * 2
			}

			function fitAbove()
			{
				return aY + 1 > h + pad * 2
			}

			placement = this.options.placement

			switch (placement)
			{
				case 'right':
					if (fitAfter()) return placement
					break
				case 'left':
					if (fitBefore()) return placement
					break
				case 'top':
					if (fitAbove()) return placement
					break
				case 'bottom':
					return placement
			}

			if (fitAfter()) return 'right'
			if (fitBefore()) return 'left'
			if (fitAbove()) return 'top'

			return 'bottom'
		}

		/**
		 * @param {boolean} quick
		 */
		reposition(quick)
		{
			if (!this.anchor)
			{
				return
			}

			if (quick === undefined)
			{
				quick = this.element.getStyle('visibility') != 'visible'
			}

			let pad = 20, actions = this.actions
			let anchorBox, aX, aY, aW, aH, anchorMiddleX, anchorMiddleY
			let size = this.element.getSize(), w = size.x, h = size.y, x, y
			let placement
			let vpCoordinates = viewport.getCoordinates()
			let bodyX = vpCoordinates.left
			let bodyY = vpCoordinates.top
			let bodyW = vpCoordinates.width
			let bodyH = vpCoordinates.height
			let arrowTransform = { top: null, left: null }, arX, arY

			anchorBox = this.computeAnchorBox()
			aX = anchorBox.x
			aY = anchorBox.y
			aW = anchorBox.w
			aH = anchorBox.h
			anchorMiddleX = aX + aW / 2 - 1
			anchorMiddleY = aY + aH / 2 - 1

			placement = this.computeBestPlacement(anchorBox, w, h)

			this.changePlacement(placement)

			if (placement == 'left' || placement == 'right')
			{
				y = Math.round(aY + (aH - h) / 2 - 1)
				x = (placement == 'left') ? aX - w + 1 : aX + aW - 1

				//
				// limit 'x' and 'y' to the limits of the document incuding a padding value.
				//

				x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
				y = y.limit(bodyY + pad - 1, bodyY + bodyH - (h + pad) - 1)
			}
			else
			{
				x = Math.round(aX + (aW - w) / 2 - 1)
				y = (placement == 'top') ? aY - h + 1 : aY + aH - 1

				//
				// limit 'x' and 'y' to the limits of the document incuding a padding value.
				//

				x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
				//y = y.limit(bodyY + pad, bodyY + bodyH - (h + pad))
			}

			// adjust arrow

			if (h > pad * 2)
			{
				if (placement == 'left' || placement == 'right')
				{
					arY = (aY + aH / 2 - 1) - y

					arY = Math.min(h - (actions ? actions.getSize().y : pad) - 10, arY)
					arY = Math.max(pad, arY)

					// adjust element Y so that the arrow is always centered on the anchor visible height

					if (arY + y - 1 != anchorMiddleY)
					{
						y -= (y + arY) - anchorMiddleY
					}

					arrowTransform.top = arY
				}
				else
				{
					arX = ((aX + aW / 2 - 1) - x).limit(pad, w - pad)

					// adjust element X so that the arrow is always centered on the anchor visible width

					if (arX + w - 1 != anchorMiddleX)
					{
						x -= (x + arX) - anchorMiddleX
					}

					arrowTransform.left = arX
				}
			}

			x = Math.floor(Math.max(50, x))
			y = Math.floor(Math.max(50, y))

			if (quick)
			{
				this.element.setStyles({ left: x, top: y })
				this.arrow.setStyles(arrowTransform)
			}
			else
			{
				this.element.morph({ left: x, top: y })
				this.arrow.morph(arrowTransform)
			}
		}

		/**
		 * @param {Function} callback
		 */
		observeAction(callback)
		{
			this.observe(ActionEvent, callback)
		}
	}

	Object.assign(Popover.prototype, Subject.prototype)

	function repositionPopovers()
	{
		Object.forEach(popovers, popover => {

			if (!popover.isVisible() || !viewport.isElementVisible(popover.element))
			{
				return
			}

			popover.reposition(true)

		})
	}

	/**
	 * Popover widget constructor.
	 */
	Brickrouge.register('Popover', (element, options) => {

		return new Popover(element, options)

	})

	Brickrouge.observeRunning(() => {

		window.addEventListener('load', repositionPopovers)
		window.addEventListener('resize', repositionPopovers)
		window.addEventListener('scroll', repositionPopovers)

	})

	return Brickrouge.Popover = Popover

})
