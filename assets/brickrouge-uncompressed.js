/*!
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var Brickrouge = {

	Utils: {

		Busy: new Class({

			startBusy: function()
			{
				if (++this.busyNest == 1) return

				this.element.addClass('busy')
			},

			finishBusy: function()
			{
				if (--this.busyNest) return

				this.element.removeClass('busy')
			}
		})
	},

	/**
	 * Update the document by adding missing CSS and JS assets.
	 *
	 * @param object assets
	 * @param function done
	 */
	updateAssets: (function()
	{
		var available_css = null
		, available_js = null

		return function (assets, done)
		{
			var css = new Array()
			, js = new Array()
			, js_count

			if (available_css === null)
			{
				available_css = []

				if (typeof(document_cached_css_assets) !== 'undefined')
				{
					available_css = document_cached_css_assets
				}

				document.id(document.head).getElements('link[type="text/css"]').each
				(
					function(el)
					{
						available_css.push(el.get('href'))
					}
				)
			}

			if (available_js === null)
			{
				available_js = []

				if (typeof(brickrouge_cached_js_assets) !== 'undefined')
				{
					available_js = brickrouge_cached_js_assets
				}

				document.id(document.html).getElements('script').each
				(
					function(el)
					{
						var src = el.get('src')

						if (src) available_js.push(src)
					}
				)
			}

			assets.css.each
			(
				function(url)
				{
					if (available_css.indexOf(url) != -1) return

					css.push(url)
				}
			);

			css.each
			(
				function(url)
				{
					new Asset.css(url)

					available_css.push(url)
				}
			);

			assets.js.each
			(
				function(url)
				{
					if (available_js.indexOf(url) != -1) return

					js.push(url)
				}
			);

			js_count = js.length

			if (!js_count)
			{
				done()

				return
			}

			js.each
			(
				function(url)
				{
					new Asset.javascript
					(
						url,
						{
							onload: function()
							{
								available_js.push(url)

								if (!--js_count)
								{
									done()
								}
							}
						}
					)
				}
			)
		}

	}) (),

	/**
	 * The `Brickrouge.Widget` namespace is used to store widgets constructors.
	 */
	Widget: {

	},

	/**
	 * Awakes sleeping widgets.
	 *
	 * Constructors defined under the `Brickrouge.Widget` namespace are traversed and for each one
	 * of them matching widgets are searched in the DOM a new widget is created using the
	 * constructor.
	 *
	 * Widgets are matched against a constructor based on the following naming convention: for a
	 * "AdjustNode" constructor, the elements matching the ".widget-adjust-node" selector are
	 * turned into widgets.
	 *
 	 * The `widget` property is not stored in the element and is used to avoid creating two
 	 * widgets with the same element.
	 *
	 * @param container This optionnal parameter can be used to limit widget awaking to a
	 * specified container, otherwise the document's body is used.
	 */
	awakeWidgets: function(container)
	{
		container = container || document.id(document.body)

		Object.each
		(
			Brickrouge.Widget,
			(
				function(constructor, key)
				{
					var cl = '.widget' + key.hyphenate()

					container.getElements(cl).each
					(
						function(el)
						{
							if (el.retrieve('widget')) return

							var widget = new constructor(el, el.get('dataset'))

							el.store('widget', widget)
						}
					)
				}
			)
		)
	}
}

/*
 * The Request.Element class requires the Request.API class provided by the ICanBoogie framework,
 * maybe we should move the Request.Element and Request.Widget clases to the Icybee CMS.
 */
if (Request.API)
{

	/**
	 * Extends Request.API to support the loading of single HTML elements.
	 */
	Request.Element = new Class
	({
		Extends: Request.API,

		onSuccess: function(response, text)
		{
			var el = Elements.from(response.rc).shift()

			if (!response.assets)
			{
				this.parent(el, response, text)

				return
			}

			Brickrouge.updateAssets
			(
				response.assets, function()
				{
					this.fireEvent('complete', [ response, text ]).fireEvent('success', [ el, response, text ]).callChain()
				}
				.bind(this)
			);
		}
	});

	/**
	 * Extends Request.Element to support loading of single widgets.
	 */
	Request.Widget = new Class
	({
		Extends: Request.Element,

		initialize: function(cl, onSuccess, options)
		{
			if (options == undefined)
			{
				options = {}
			}

			options.url = 'widgets/' + cl
			options.onSuccess = onSuccess

			this.parent(options)
		}
	})
}

/**
 * Returns the widget associate with the element.
 *
 * If the element has no widget attached yet it will be created if a matching constructor if
 * available.
 */
Element.Properties.widget = {

	get: function()
	{
		var widget = this.retrieve('widget')
		, type
		, constructorName
		, constructor

		if (!widget)
		{
			type = this.className.match(/widget(-\S+)/)

			if (type && type.length)
			{
				constructorName = type[1].camelCase()
				constructor = Brickrouge.Widget[constructorName]

				if (!constructor)
				{
					throw new Error("Constructor \"" + constructor + "\"is not defined to create widgets of type \"" + type + "\"")
				}

				widget = new constructor(this, this.get('dataset'))

				this.store('widget', widget)
			}
		}

		return widget;
	}
};

/**
 * Returns the dataset of the element.
 *
 * The dataset is created by readding and aggregatting value defined by the data-* attributes.
 */
Element.Properties.dataset = {

	get: function() {

		var dataset = {}
		, attributes = this.attributes
		, i
		, y
		, attr

		for (i = 0, y = attributes.length ; i < y ; i++)
		{
			attr = attributes[i]

			if (!attr.name.match(/^data-/)) continue;

			dataset[attr.name.substring(5).camelCase()] = attr.value
		}

		return dataset
	}
}

/*
 * We make sure that the document body element is properly extended by MooTools.
 */
document.id(document.body)

/**
 * Calls the Brickrouge.awakeWidgets when the `elementsready` event is fired on the document.
 */
document.addEvent('elementsready', function(ev) {

	Brickrouge.awakeWidgets(ev.target)

})

/**
 * The "elementsready" event is fired for elements to be initialized, to become alive thanks to the
 * magic of Javascript. This event is usually fired when new widgets are added to the DOM.
 */
window.addEvent('domready', function() {

	document.fireEvent('elementsready', { target: document.id(document.body) })

})/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Support for asynchronous forms.
 */
Brickrouge.Form = new Class({

	Implements: [ Options, Events ],

	options:
	{
		url: null,
		useXHR: false
	},

	initialize: function(el, options)
	{
		this.element = document.id(el)
		this.setOptions(options)

		if (this.options.useXHR || (options && (options.onRequest || options.onComplete || options.onFailure || options.onSuccess)))
		{
			this.element.addEvent
			(
				'submit', function(ev)
				{
					ev.stop()
					this.submit()
				}
				.bind(this)
			)
		}
	},

	alert: function(messages, type)
	{
		var original, alert = this.element.getElement('div.alert-' + type) || new Element('div.alert.alert-' + type, { html: '<a href="#close" class="close">×</a>'})

		if (typeOf(messages) == 'string')
		{
			messages = [ messages ]
		}
		else if (typeOf(messages) == 'object')
		{
			original = messages

			messages = []

			Object.each
			(
				original, function(message, id)
				{
					if (typeOf(id) == 'string' && id != '_base')
					{
						var parent
						, field
						, el = this.element.elements[id]
						, i

						if (typeOf(el) == 'collection')
						{
							parent = el[0].getParent('div.radio-group')
							field = parent.getParent('.field')

							if (parent)
							{
								parent.addClass('error')
							}
							else
							{
								for (i = 0, j = el.length ; i < j ; i++)
								{
									el[i].addClass('error')
								}
							}
						}
						else
						{
							el.addClass('error')
							field = el.getParent('.field')
						}

						if (field)
						{
							field.addClass('error')
						}
					}

					if (!message || message === true)
					{
						return
					}

					messages.push(message)
				},

				this
			)
		}

		if (!messages.length)
		{
			return
		}

		messages.each
		(
			function(message)
			{
				alert.adopt(new Element('p', { html: message }))
			}
		)

		if (!alert.parentNode)
		{
			alert.inject(this.element, 'top')
		}
	},

	clearAlert: function()
	{
		var alerts = this.element.getElements('div.alert')

		if (alerts)
		{
			alerts.destroy()
		}

		this.element.getElements('.error').removeClass('error')
	},

	submit: function()
	{
		this.fireEvent('submit', {})
		this.getOperation().send(this.element)
	},

	getOperation: function()
	{
		if (this.operation)
		{
			return this.operation
		}

		return this.operation = new Request.JSON
		({
			url: this.options.url || this.element.action,

			onRequest: this.request.bind(this),
			onComplete: this.complete.bind(this),
			onSuccess: this.success.bind(this),
			onFailure: this.failure.bind(this)
		})
	},

	request: function()
	{
		this.clearAlert()
		this.fireEvent('request', arguments)
	},

	complete: function()
	{
		this.fireEvent('complete', arguments)
	},

	success: function(response)
	{
		if (response.success)
		{
			this.alert(response.success, 'success')
		}

		this.onSuccess(response)
	},

	onSuccess: function(response)
	{
		this.fireEvent('success', arguments)
	},

	failure: function(xhr)
	{
		var response = JSON.decode(xhr.responseText)

		if (response && response.errors)
		{
			this.alert(response.errors, 'error')
		}

		this.fireEvent('failure', arguments)
	}
})/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Destroy the alert message when its close icon is clicked.
 *
 * If the alert message is in a FORM element the "error" class is removed from its elements.
 */
document.body.addEvent('click:relay(.alert a.close)', function(ev, target) {

	var form = target.getParent('form')

	ev.stop()

	if (form) form.getElements('.error').removeClass('error')

	target.getParent('.alert').destroy()
})/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function() {

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

	window.addEvent('click:relay(' + toggleSelector + ')', function(ev, el) {

		ev.stop()
		toggle.bind(el)()
	})
} ()
/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Activates the target tab content of a tab.
 */
document.body.addEvent('click:relay(.nav-tabs a)', function(ev, el) {

	var target = document.id(el.get('href').substring(1))

	if (!target) return

	target.getParent('.tab-content').getChildren().each(function(pane) {

		pane[target == pane ? 'addClass' : 'removeClass']('active')
	})
})/*
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
		this.element = $(el)
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
		this.anchor = $(anchor)

		if (!this.anchor)
		{
			this.anchor = $(document.body).getElement(anchor)
		}
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
			$(this.iframe.contentWindow).addEvents
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
			var contentWindow = $(this.iframe.contentWindow)

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
		actions = [ new Element('button.cancel[data-action="cancel"]', { html: 'Cancel' }), new Element('button.primary[data-action="ok"]', { html: 'Ok' }) ]
	}

	if (actions)
	{
		inner.adopt(new Element('div.popover-actions').adopt(actions))
	}

	popover = new Element('div.popover').adopt([ new Element('div.arrow'), inner ])

	return new Brickrouge.Popover(popover, options)
}

/**
 * Popover widget constructor.
 */
Brickrouge.Widget.Popover = Brickrouge.Popover

/**
 * Event delegation for A elements with a `rel="popover"` attribute.
 */
document.id(document.body).addEvents
({
	'mouseenter:relay([rel="popover"])': function(ev, target)
	{
		var popover
		, options

		popover = target.retrieve('popover')

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
})/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

Brickrouge.Widget.Searchbox = new Class({

	Implements: Brickrouge.Utils.Busy,

	initialize: function(el, options)
	{
		this.element = document.id(el)
	}
})
/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Animates a carousel.
 */
Brickrouge.Carousel = new Class({

	Implements: [ Options, Events ],

	options: {

		autoplay: false,
		delay: 6000,
		method: 'fade'
	},

	initialize: function(el, options)
	{
		this.element = $(el)
		this.setOptions(options)
		this.inner = el.getElement('.carousel-inner')
		this.slides = this.inner.getChildren()
		this.position = 0
		this.limit = this.slides.length
		this.timer = null

		if (this.options.method)
		{
			this.setMethod(this.options.method)

			if (this.method.initialize)
			{
				this.method.initialize.apply(this)
			}
		}

		this.element.addEvents({

			'click:relay(.carousel-control.left)': this.prev.bind(this),
			'click:relay(.carousel-control.right)': this.next.bind(this),

			mouseenter: this.pause.bind(this),
			mouseleave: this.resume.bind(this)

		})

		this.resume()
	},

	setMethod: function(method)
	{
		if (typeOf(method) == 'string')
		{
			method = Brickrouge.Carousel.Methods[method]
		}

		this.method = method

		if (method.next) this.next = method.next
		if (method.prev) this.prev = method.prev
	},

	play: function()
	{
		if (this.timer) return

		this.timer = (function() {

			this.setPosition(this.position + 1)

		}).periodical(this.options.delay, this)

		this.fireEvent('play', { position: this.position, slide: this.slides[this.position] })
	},

	pause: function()
	{
		if (!this.timer) return

		clearInterval(this.timer)
		this.timer = null

		this.fireEvent('pause', { position: this.position, slide: this.slides[this.position] })
	},

	resume: function()
	{
		if (!this.options.autoplay) return

		this.play()
	},

	setPosition: function(position)
	{
		position = position % this.limit

		if (position == this.position) return

		this.method.go.apply(this, [ position ])

		this.fireEvent('position', { position: this.position, slide: this.slides[this.position] })
	},

	prev: function()
	{
		this.setPosition(this.position ? this.position - 1 : this.limit - 1)
	},

	next: function()
	{
		this.setPosition(this.position == this.limit ? 0 : this.position + 1)
	}
})

/**
 * Carousel methods.
 */
Brickrouge.Carousel.Methods = {

	fade: {

		initialize: function()
		{
			this.slides.each(function(slide, i) {

				slide.setStyles({

					left: 0,
					top: 0,
					position: 'absolute',
					opacity: i ? 0 : 1,
					visibility: i ? 'hidden' : 'visible',
				})
			})
		},

		go: function(position)
		{
			var slideOut = this.slides[this.position]
			, slideIn = this.slides[position]

			slideIn.setStyles({ opacity: 0, visibility: 'visible' }).inject(slideOut, 'after').fade('in')

			this.position = position
		}
	},

	columns: {

		initialize: function()
		{
			this.working = false
			this.fitting = 0
			this.childWidth = 0

			var offset = 0
			, totalWidth = 0
			, width = 0
			, visible_w = this.element.getSize().x

			this.view = new Element
			(
				'div',
				{
					'styles':
					{
						position: 'absolute',
						top: 0,
						left: 0,
						height: this.element.getStyle('height'),
					}
				}
			);

			this.view.adopt(this.slides);
			this.view.inject(this.inner);
			this.view.set('tween', { property: 'left' });

			this.slides.each
			(
				function(el)
				{
					if (el.get('data-url'))
					{
						el.setStyle('cursor', 'pointer')
					}

					var w = el.getSize().x + el.getStyle('margin-left').toInt() + el.getStyle('margin-right').toInt()

					el.setStyles
					({
						'position': 'absolute',
						'top': 0,
						'left': offset
					})

					offset += w
					totalWidth += w
					width = Math.max(width, w)
				},

				this
			);

			this.childWidth = width
			this.fitting = (visible_w / width).floor()
			this.view.setStyle('width', totalWidth)
		},

		go: function(position)
		{
			var n = this.limit
			, diff = this.position - position
			, to_uncover = null
			, left = 0

			if (this.working)
			{
				return;
			}

			this.working = true;

//				console.log('request position: %d (current: %d), diff: %d (count: %d)', position, this.position, diff, n);

			to_uncover = (diff < 0) ? this.position + this.fitting : this.position - diff

			if (to_uncover < 0)
			{
//					console.log('uncover out of range %d (%d)', to_uncover, n);

				to_uncover = n + to_uncover
			}
			else if (to_uncover > n - 1)
			{
//					console.log('uncover out of range %d (%d)', to_uncover, n);

				to_uncover = to_uncover - n
			}

			if (position < 0)
			{
				position = n - diff
			}
			else
			{
				position = position % n
			}

			this.position = position

//				console.log('final position: %d (%d), final uncover: %d', position, this.position, to_uncover);

			left = diff < 0 ? this.childWidth * this.fitting : -this.childWidth

//				console.log('left: ', left);

			this.slides[to_uncover].setStyle('left', left)

			this.view.get('tween').start(this.childWidth * diff).chain
			(
				function()
				{
					var i = position
					, offset = 0
					, w = this.childWidth

					for ( ; i < n ; i++, offset += w)
					{
						this.slides[i].setStyle('left', offset)
					}

					for (i = 0 ; i < position ; i++, offset += w)
					{
						this.slides[i].setStyle('left', offset);
					}

					this.view.setStyle('left', 0);

					this.working = false;
				}
				.bind(this)
			);
		},

		next: function()
		{
			this.setPosition(this.position + 1)
		},

		prev: function()
		{
			this.setPosition(this.position - 1)
		}
	}
}
