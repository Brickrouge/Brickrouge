/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A subtextual information attached to an anchor.
 *
 * The popover element is a floating element attached to an anchor. It repositions itself to
 * follow its anchor, and may change its placement according to the available space around it.
 */
Brickrouge.Popover = new Class({

	Implements: [ Events, Options ],

	options:
	{
		anchor: null,
		placement: null,
		visible: false,
		fitContent: false,
		iframe: null
	},

	initialize: function(el, options)
	{
		this.element = document.id(el)
		this.setOptions(options)
		this.arrow = this.element.getElement('.arrow')
		this.actions = this.element.getElement('.popover-actions')
		this.repositionCallback = this.reposition.bind(this, false)
		this.quickRepositionCallback = this.reposition.bind(this, true)

		this.iframe = this.options.iframe

		if (this.options.anchor)
		{
			this.attachAnchor(this.options.anchor)
		}

		this.tween = null

		if (this.options.animate)
		{
			this.tween = new Fx.Tween(this.element, { property: 'opacity', link: 'cancel', duration: 'short' })
		}

		if (this.options.fitContent)
		{
			this.element.addClass('fit-content')
		}

		this.element.addEvent('click', this.onClick.bind(this))

		if (this.options.visible)
		{
			this.show()
		}
	},

	attachAnchor: function(anchor)
	{
		this.anchor = document.id(anchor)

		if (!this.anchor)
		{
			this.anchor = document.body.getElement(anchor)
		}

		this.reposition(true)
	},

	onClick: function(ev)
	{
		var target = ev.target

		if (target.tagName == 'BUTTON' && target.getParent('.popover-actions'))
		{
			this.fireAction({ action: target.get('data-action'), popover: this, ev: ev })
		}
	},

	fireAction: function(params)
	{
		this.fireEvent('action', arguments)
	},

	changePlacement: function(placement)
	{
		this.element.removeClass('before')
		.removeClass('after')
		.removeClass('above')
		.removeClass('below')
		.addClass(placement)
	},

	show: function()
	{
		this.element.setStyles({ display: 'block', visibility: 'hidden' })

		window.addEvents
		({
			'load': this.quickRepositionCallback,
			'resize': this.quickRepositionCallback,
			'scroll': this.repositionCallback
		})

		if (this.iframe)
		{
			document.id(this.iframe.contentWindow).addEvents
			({
				'load': this.quickRepositionCallback,
				'resize': this.quickRepositionCallback,
				'scroll': this.repositionCallback
			})
		}

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
	},

	hide: function()
	{
		window.removeEvent('load', this.quickRepositionCallback)
		window.removeEvent('resize', this.quickRepositionCallback)
		window.removeEvent('scroll', this.repositionCallback)

		if (this.iframe)
		{
			var contentWindow = document.id(this.iframe.contentWindow)

			contentWindow.removeEvent('load', this.quickRepositionCallback)
			contentWindow.removeEvent('resize', this.quickRepositionCallback)
			contentWindow.removeEvent('scroll', this.repositionCallback)
		}

		if (this.options.animate)
		{
			this.tween.start(0).chain
			(
				function()
				{
					this.element.setStyle('display', '')
				}
			)
		}
		else
		{
			this.element.setStyle('display', '')
		}
	},

	computeAnchorBox: function()
	{
		var anchor = this.anchor
		, anchorCoords
		, iframe = this.iframe
		, iframeCoords
		, iHTML
		, visibleH
		, visibleW
		, hiddenTop
		, hiddenLeft

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
	},

	/**
	 * Compute best placement of the popover relative to its anchor.
	 *
	 * If the placement is defined in the option it is used unless there is not enough available
	 * space for the popover.
	 *
	 * Emplacements are tried in the following order: 'after', 'before', 'above' and fallback to
	 * 'below'.
	 */
	computeBestPlacement: function(anchorBox, w, h)
	{
		var html = document.body.parentNode
		, bodyCompleteW = html.scrollWidth
		, aX = anchorBox.x
		, aY = anchorBox.y
		, aW = anchorBox.w
		, placement
		, pad = 20

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
			case 'after': if (fitAfter()) return placement; break
			case 'before': if (fitBefore()) return placement; break
			case 'above': if (fitAbove()) return placement; break
			case 'below': return placement
		}

		if (fitAfter()) return 'after'
		if (fitBefore()) return 'before'
		if (fitAbove()) return 'above'

		return 'below'
	},

	reposition: function(quick)
	{
		if (!this.anchor)
		{
			return
		}

		if (quick === undefined)
		{
			quick = this.element.getStyle('visibility') != 'visible'
		}

		var pad = 20, actions = this.actions,
		anchorBox, aX, aY, aW, aH, anchorMiddleX, anchorMiddleY,
		size = this.element.getSize(), w = size.x , h = size.y, x, y,
		placement,
		body = document.id(document.body),
		bodySize = body.getSize(),
		bodyScroll = body.getScroll(),
		bodyX = bodyScroll.x,
		bodyY = bodyScroll.y,
		bodyW = bodySize.x,
		bodyH = bodySize.y,
		arrowTransform = { top: null, left: null }, arX, arY

		anchorBox = this.computeAnchorBox()
		aX = anchorBox.x
		aY = anchorBox.y
		aW = anchorBox.w
		aH = anchorBox.h
		anchorMiddleX = aX + aW / 2 - 1
		anchorMiddleY = aY + aH / 2 - 1

		placement = this.computeBestPlacement(anchorBox, w, h)

		this.changePlacement(placement)

		if (placement == 'before' || placement == 'after')
		{
			y = Math.round(aY + (aH - h) / 2 - 1)
			x = (placement == 'before') ? aX - w + 1 : aX + aW - 1

			//
			// limit 'x' and 'y' to the limits of the document incuding a padding value.
			//

			x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
			y = y.limit(bodyY + pad - 1, bodyY + bodyH - (h + pad) - 1)
		}
		else
		{
			x = Math.round(aX + (aW - w) / 2 - 1)
			y = (placement == 'above') ? aY - h + 1 : aY + aH - 1

			//
			// limit 'x' and 'y' to the limits of the document incuding a padding value.
			//

			x = x.limit(bodyX + pad - 1, bodyX + bodyW - (w + pad) - 1)
			//y = y.limit(bodyY + pad, bodyY + bodyH - (h + pad))
		}

		// adjust arrow

		if (h > pad * 2)
		{
			if (placement == 'before' || placement == 'after')
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
})

/**
 * Creates a popover element using the provided options.
 *
 * @param options
 *
 * @returns {Brickrouge.Popover}
 */
Brickrouge.Popover.from = function(options)
{
	var popover, title = options.title,
	content = options.content,
	actions = options.actions,
	inner = new Element('div.popover-inner')

	if (title)
	{
		inner.adopt(new Element('h3.popover-title', { 'html': title }))
	}

	if (typeOf(content) == 'element')
	{
		inner.adopt(new Element('div.popover-content').adopt(content))
	}
	else
	{
		inner.adopt(new Element('div.popover-content', { 'html': content }))
	}

	if (actions == 'boolean')
	{
		actions = [ new Element('button.cancel[data-action="cancel"]', { html: 'Cancel' })
		, new Element('button.primary[data-action="ok"]', { html: 'Ok' }) ]
	}

	if (actions)
	{
		inner.adopt(new Element('div.popover-actions').adopt(actions))
	}

	popover = new Element('div.popover').adopt([ new Element('div.arrow'), inner ])

	return (new Brickrouge.Popover(popover, options))
}

/**
 * Popover widget constructor.
 */
Brickrouge.Widget.Popover = Brickrouge.Popover

/**
 * Event delegation for A elements with a `rel="popover"` attribute.
 */
document.body.addEvents
({
	'mouseenter:relay([rel="popover"])': function(ev, target)
	{
		var popover = target.retrieve('popover')
		, options

		if (!popover)
		{
			options = target.get('dataset')

			options.anchor = target
			popover = Brickrouge.Popover.from(options)

			document.body.appendChild(popover.element)

			target.store('popover', popover)
		}

		popover.show()
	},

	'mouseleave:relay([rel="popover"])': function(ev, target)
	{
		var popover = target.retrieve('popover')

		if (!popover) return

		popover.hide()
	}
})