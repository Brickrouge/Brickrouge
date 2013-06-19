/*!
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function() {

	var WIDGET_CONSTRUCTOR_ATTRIBUTE = 'data-widget-constructor'
	, WIDGET_CONSTRUCTOR_SELECTOR = '[' + WIDGET_CONSTRUCTOR_ATTRIBUTE + ']'

	/**
	 * Construct the widget associated with an element.
	 *
	 * @param element The element
	 */
	function constructWidget(element)
	{
		var constructorName = element.get(WIDGET_CONSTRUCTOR_ATTRIBUTE)

		if (!constructorName)
		{
			throw new Error("The " + WIDGET_CONSTRUCTOR_ATTRIBUTE + " attribute is not defined.")
		}

		var constructor = Brickrouge.Widget[constructorName]

		if (!constructor)
		{
			throw new Error("Undefined constructor: " + constructorName)
		}

		element.store('widget', true)

		var widget = new constructor(element, element.get('dataset'))

		element.store('widget', widget)

		return widget
	}

	/**
	 * Returns the widget associate with the element.
	 *
	 * If the element has no widget attached yet it will be created if a matching constructor is
	 * available.
	 */
	Element.Properties.widget = {

		get: function()
		{
			var widget = this.retrieve('widget')

			if (!widget)
			{
				widget = constructWidget(this)

				window.fireEvent('brickrouge.construct', { constructed: [ this ] })
			}

			return widget
		}
	}

	/**
	 * Construct widgets.
	 *
	 * Widgets are constructed by creating a new object using a constructor, an element and
	 * options. The constructor name is defined by the `data-widget-constructor` attribute of the
	 * element, and the dataset of the element is used as options.
	 *
	 * Elements are collected using the {@link WIDGET_CONSTRUCTOR_SELECTOR} selector from the
	 * deepest nodes to the root. The function uses the custom `widget` property to inderectly
	 * create the widgets.
	 *
	 * The `brickrouge.construct` event is fired on the `window` with the elements which had
	 * widgets constructed for. The event is only fired if widgets were constructed.
	 *
	 * @param element This optional parameter can be used to limit widget construction to a
	 * specified element. If the element if not defined or is empty the document body is used
	 * instead.
	 */
	function constructWidgets(element)
	{
		element = element || document.body

		var elements = element.getElements(WIDGET_CONSTRUCTOR_SELECTOR)
		, constructed = []

		if (element.match(WIDGET_CONSTRUCTOR_SELECTOR))
		{
			elements.unshift(element)
		}

		elements.reverse().each(function(el) {

			if (el.retrieve('widget')) return

			constructWidget(el)

			constructed.push(el)
		})

		if (constructed.length)
		{
			window.fireEvent('brickrouge.construct', { constructed: constructed })
		}
	}

	/**
	 * Updates the document assets then calls a callback function.
	 *
	 * @param assets An object with a 'css' and a 'js' array defining the assets required.
	 * @param done An optional callback to call once the required assets have been loaded.
	 */
	var updateAssets = (function() {

		var available_css = null
		, available_js = null

		return function(assets, done)
		{
			var css = new Array()
			, js = new Array()
			, js_count

			if (available_css === null)
			{
				available_css = []

				if (typeof(brickrouge_cached_css_assets) !== 'undefined')
				{
					available_css = brickrouge_cached_css_assets
				}

				document.id(document.head).getElements('link[type="text/css"]').each(function(el) {

					available_css.push(el.get('href'))
				})
			}

			if (available_js === null)
			{
				available_js = []

				if (typeof(brickrouge_cached_js_assets) !== 'undefined')
				{
					available_js = brickrouge_cached_js_assets
				}

				document.id(document.html).getElements('script').each(function(el) {

					var src = el.get('src')

					if (src) available_js.push(src)
				})
			}

			assets.css.each(function(url) {

				if (available_css.indexOf(url) != -1) return
				css.push(url)
			})

			css.each(function(url) {

				new Asset.css(url)
				available_css.push(url)
			})

			assets.js.each(function(url) {

				if (available_js.indexOf(url) != -1) return
				js.push(url)
			})

			js_count = js.length

			if (!js_count)
			{
				done(); return
			}

			js.each(function(url) {

				new Asset.javascript(url, {

					onload: function() {

						available_js.push(url)
						if (!--js_count) done()
					}
				})
			})
		}
	}) ()

	this.Brickrouge = {

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
		 * The `Brickrouge.Widget` namespace is used to store widgets constructors.
		 */
		Widget: {

		},

		/**
		 * Constructs the widgets defined in the document.
		 *
		 * Before the widgets are constructed the event `brickrouge.update` is fired on the
		 * `window`.
		 *
		 * Note: A widget is only constructed once.
		 *
		 * @param el The element updating the document.
		 */
		updateDocument: function(el) {

			el = el || document.body

			window.fireEvent('brickrouge.update', { target: el })

			constructWidgets(el)
		},

		/**
		 * Update the document by adding missing CSS and JS assets.
		 *
		 * @param object assets
		 * @param function done
		 */
		updateAssets: updateAssets
	}

} ()

/*
 * The Request.Element class requires the Request.API class provided by the ICanBoogie framework,
 * maybe we should move the Request.Element and Request.Widget classes to the Icybee CMS.
 */
if (Request.API)
{
	/**
	 * Extends Request.API to support the loading of single HTML elements.
	 */
	Request.Element = new Class({

		Extends: Request.API,

		onSuccess: function(response, text)
		{
			var el = Elements.from(response.rc).shift()

			if (!response.assets)
			{
				this.parent(el, response, text)

				return
			}

			Brickrouge.updateAssets(response.assets, function() {

				this.fireEvent('complete', [ response, text ]).fireEvent('success', [ el, response, text ]).callChain()

			}.bind(this))
		}
	})

	/**
	 * Extends Request.Element to support loading of single widgets.
	 */
	Request.Widget = new Class({

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
 * Returns the dataset of the element.
 *
 * The dataset is created by reading and aggregating value defined by the data-* attributes.
 */
Element.Properties.dataset = {

	get: function() {

		var dataset = {}
		, attributes = this.attributes
		, i = 0
		, y = attributes.length
		, attr

		for ( ; i < y ; i++)
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

/*
 * Fix for IE to fire 'click' events on the window object.
 */
if (Browser.ie)
{
	document.body.addEvent('click', function(ev) {

		window.fireEvent('click', ev)

	})
}

/**
 * Invokes the {@link Brickrouge.updateDocument} method on `domready` with `document.body`
 * as argument.
 */
window.addEvent('domready', function() {

	Brickrouge.updateDocument(document.body)

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
		useXHR: false,
		replaceOnSuccess: false
	},

	initialize: function(el, options)
	{
		this.element = document.id(el)
		this.setOptions(options)

		if (this.options.replaceOnSuccess)
		{
			this.options.useXHR = true
		}

		if (this.options.useXHR || (options && (options.onRequest || options.onComplete || options.onFailure || options.onSuccess)))
		{
			this.element.addEvent('submit', function(ev) {

				ev.stop()
				this.submit()
			}
			.bind(this))
		}
	},

	alert: function(messages, type)
	{
		var original = messages
		, alert = this.element.getElement('div.alert-' + type)
		|| new Element('div.alert.alert-' + type, { html: '<button class="close" data-dismiss="alert">×</button>' })

		if (typeOf(messages) == 'string')
		{
			messages = [ messages ]
		}
		else if (typeOf(messages) == 'object')
		{
			messages = []

			Object.each(original, function(message, id) {

				if (typeOf(id) == 'string' && id != '_base')
				{
					var parent
					, field = null
					, el = document.id(this.element.elements[id])
					, i

					if (typeOf(el) == 'collection')
					{
						parent = document.id(el[0]).getParent('div.radio-group')
						field = parent.getParent('.control-group')

						if (parent)
						{
							parent.addClass('error')
						}
						else
						{
							for (i = 0, j = el.length ; i < j ; i++)
							{
								document.id(el[i]).addClass('error')
							}
						}
					}
					else if (el)
					{
						el.addClass('error')
						field = el.getParent('.control-group')
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

			}, this)
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

		this.insertAlert(alert)
	},

	insertAlert: function(alert)
	{
		if (alert.hasClass('alert-success') && this.options.replaceOnSuccess)
		{
			alert.getElement('[data-dismiss="alert"]').dispose()
			alert.addClass('undissmisable')
			alert.inject(this.element, 'before')

			this.element.addClass('hidden')
		}
		else if (!alert.getParent())
		{
			alert.inject(this.element, 'top')
		}
	},

	clearAlert: function()
	{
		var alerts = this.element.getElements('div.alert:not(.undissmisable)')

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
			method: this.element.get('method') || 'GET',

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
		if (response.message)
		{
			this.alert(response.message, 'success')
		}

		this.onSuccess(response)
	},

	onSuccess: function(response)
	{
		this.fireEvent('success', arguments)
	},

	failure: function(xhr)
	{
		var response = {}

		try
		{
			response = JSON.decode(xhr.responseText)

			if (response.errors)
			{
				this.alert(response.errors, 'error')
			}

			if (response.exception)
			{
				alert(response.exception)
			}
		}
		catch (e)
		{
			if (console)
			{
				console.log(e)
			}

			alert(xhr.statusText)
		}

		this.fireEvent('failure', [ xhr, response ])
	}
})

Brickrouge.Form.STORED_KEY_NAME = '_brickrouge_form_key'/*
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
})

document.body.addEvent('click:relay([data-dismiss="alert"])', function(ev, target) {

	var alert = target.getParent('.alert')
	var form = alert.getParent('form')

	ev.stop()

	if (form) form.getElements('.error').removeClass('error')

	alert.destroy()
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
	, skipEvent = false

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

	window.addEvent('click:relay(' + toggleSelector + ')', function(ev, el) {

		if (ev.rightClick) return

		ev.stop()
		skipEvent = true
		toggle.apply(el)
	})

	/*
	 * Clears all menus when the user clicks away.
	 */
	window.addEvent('click', function(ev) {

		if (skipEvent)
		{
			skipEvent = false
			return
		}

		clearMenus()
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

/**
 * Distributes the remaining space after the last tab between tabs.
 *
 * The distribution only applies to tabs which container have the `nav-tabs-fill` class.
 */
window.addEvent('brickrouge.update', function() {

	document.body.getElements('.nav-tabs-fill').each(function(nav) {

		var tabs = nav.getElements('a')
		, last = tabs[tabs.length - 1]
		, lastCoordinates = last.getCoordinates(nav)
		, navSize = nav.getSize()
		, remain = (navSize.x - (lastCoordinates.left + lastCoordinates.width))
		, add = (remain / tabs.length / 2)|0
		, addFinal = remain - (add * tabs.length * 2)

		last.setStyle('padding-right', last.getStyle('padding-right').toInt() + addFinal)

		tabs.each(function(tab) {

			tab.setStyle('padding-left', tab.getStyle('padding-left').toInt() + add)
			tab.setStyle('padding-right', tab.getStyle('padding-right').toInt() + add)

		})
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
 *
 * @property element Element The popover element.
 */
Brickrouge.Popover = new Class({

	Implements: [ Events, Options ],

	options:
	{
		anchor: null,
		animate: false,
		placement: null,
		visible: false,
		fitContent: false,
		loveContent: false,
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

		el = this.element
		options = this.options

		this.iframe = options.iframe

		if (options.anchor)
		{
			this.attachAnchor(options.anchor)
		}

		this.tween = null

		if (options.animate)
		{
			this.tween = new Fx.Tween(el, { property: 'opacity', link: 'cancel', duration: 'short' })
		}

		if (options.fitContent || options.loveContent)
		{
			el.addClass('fit-content')
		}

		if (options.loveContent)
		{
			el.addClass('love-content')
		}

		el.addEvent('click:relay(.popover-actions [data-action])', function(ev, target) {

			this.fireAction({ action: target.get('data-action'), popover: this, event: ev })

		}.bind(this))

		if (options.visible)
		{
			this.show()
		}
	},

	fireAction: function(params)
	{
		this.fireEvent('action', arguments)
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

		document.body.appendChild(this.element)
		Brickrouge.updateDocument(this.element)

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
		var hide = function() {

			this.element.setStyle('display', '')
			this.element.dispose()

		}.bind(this)

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
			this.tween.start(0).chain(hide)
		}
		else
		{
			hide()
		}
	},

	isVisible: function()
	{
		return this.element.getStyle('visibility') == 'visible' && this.element.getStyle('display') != 'none'
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
 * @return Brickrouge.Popover
 */
Brickrouge.Popover.from = function(options)
{
	var popover
	, title = options.title
	, content = options.content
	, actions = options.actions
	, inner = new Element('div.popover-inner')

	if (title)
	{
		inner.adopt(new Element('h3.popover-title', { 'html': title }))
	}

	if (typeOf(content) == 'string')
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

	if (actions == 'boolean')
	{
		actions = [ new Element('button.btn.btn-cancel[data-action="cancel"]', { html: Locale.get('Popover.cancel') || 'Cancel' })
		, new Element('button.btn.btn-primary[data-action="ok"]', { html: Locale.get('Popover.ok') || 'Ok' }) ]
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
 * Event delegation for elements with a `rel="popover"` attribute.
 */
document.body.addEvents({

	'mouseenter:relay([rel="popover"])': function(ev, target)
	{
		var popover = target.retrieve('popover')
		, options

		if (!popover)
		{
			options = target.get('dataset')
			options.anchor = target
			popover = Brickrouge.Popover.from(options)

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

!function() {

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
			this.element.getElement('.tooltip-inner').set(this.options.html ? 'html' : 'text', content)

			;['fade', 'in', 'top', 'bottom', 'left', 'right'].each(this.element.removeClass, this.element)
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
				, map = anchor.getParent()
				, name = map.id || map.name
				, image = document.body.getElement('[usemap="#' + name +'"]')

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
				el.addClass('fade')
			}

			if (typeOf(placement) == 'function')
			{
				placement = placement.call(this, el, anchor)
			}

			inside = /in/.test(placement)

			el.dispose().setStyles({ top: 0, left: 0, display: 'block' }).inject(inside ? this.anchor : document.body)

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

			el.setStyles(tp).addClass(placement).addClass('in')
		},

		hide: function()
		{
			var el = this.element

			opened.erase(this)

			el.removeClass('in')
			el.dispose()
		}
	})

	Brickrouge.Tooltip.hideAll = function() {

		Array.slice(opened).each(function(tooltip) {

			tooltip.hide()

		})
	}

} ()

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

		autodots: false,
		autoplay: false,
		delay: 6000,
		method: 'fade'
	},

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.setOptions(options)
		this.inner = el.getElement('.carousel-inner')
		this.slides = this.inner.getChildren()
		this.limit = this.slides.length
		this.position = 0
		this.timer = null

		if (this.options.method)
		{
			this.setMethod(this.options.method)

			if (this.method.initialize)
			{
				this.method.initialize.apply(this)
			}
		}

		if (this.options.autodots)
		{
			this.setDots(this.slides.length)
		}

		this.dots = el.getElements('.carousel-dots .dot')

		if (!this.dots.length)
		{
			this.dots = null
		}

		if (this.dots)
		{
			this.dots[0].addClass('active')
		}

		el.addEvents({

			'click:relay([data-slide="prev"])': function(ev) {

				ev.stop()
				this.prev()

			}.bind(this),

			'click:relay([data-slide="next"])': function(ev) {

				ev.stop()
				this.next()

			}.bind(this),

			'click:relay([data-position])': function(ev, el) {

				ev.stop()
				this.setPosition(el.get('data-position'))

			}.bind(this),

			'click:relay([data-link])': function(ev, el) {

				var link = el.get('data-link')

				if (!link) return

				document.location = link
			},

			mouseenter: this.pause.bind(this),
			mouseleave: this.resume.bind(this)

		})

		this.resume()
	},

	setDots: function(number)
	{
		var dots = new Element('div.carousel-dots')
		, replaces = this.element.getElement('.carousel-dots')

		for (var i = 0 ; i < number ; i++)
		{
			dots.adopt(new Element('div.dot', { html: '&bull;', 'data-position': i }))
		}

		if (replaces)
		{
			dots.replaces(replaces)
		}
		else
		{
			this.element.adopt(dots)
		}
	},

	setMethod: function(method)
	{
		if (typeOf(method) == 'string')
		{
			var m = Brickrouge.Carousel.Methods[method]

			if (m === undefined)
			{
				throw new Error('Carousel method is not defined: ' + method)
			}

			method = m
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

	setPosition: function(position, direction)
	{
		position = position % this.limit

		if (position == this.position) return

		this.method.go.apply(this, [ position, direction ])

		if (this.dots)
		{
			this.dots.removeClass('active')
			this.dots[position].addClass('active')
		}

		this.fireEvent('position', { position: this.position, slide: this.slides[this.position] })
	},

	prev: function()
	{
		this.setPosition(this.position ? this.position - 1 : this.limit - 1, -1)
	},

	next: function()
	{
		this.setPosition(this.position == this.limit ? 0 : this.position + 1, 1)
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

	slide: {

		initialize: function()
		{
			var size = this.inner.getSize()
			, w = size.x
			, h = size.y
			, view = new Element('div', { styles: { position: 'absolute', left: 0, top: 0, width: w * 2, height: h }})

			this.w = w
			this.h = h
			this.view = view

			view.adopt(this.slides)
			view.set('tween', { property: 'left', onComplete: Brickrouge.Carousel.Methods.slide.onComplete.bind(this) })

			this.slides.each(function(slide, i) {

				slide.setStyles({ position: 'absolute', left: w * i, top: 0 })

				if (i)
				{
					slide.setStyle('display', 'none')
				}
			})

			this.inner.adopt(view)
		},

		go: function(position, direction)
		{
			var slideIn = this.slides[position]
			, slideOut = this.slides[this.position]

			if (!direction)
			{
				direction = position - this.position
			}

			this.view.setStyle('left', 0)
			slideOut.setStyle('left', 0)
			slideIn.setStyles({ display: '', left: direction > 0 ? this.w : -this.w })

			this.view.tween(direction > 0 ? -this.w : this.w)

			this.position = position
		},

		onComplete: function(ev)
		{
			var current = this.slides[this.position]

			this.slides.each(function(slide) {

				if (slide == current) return

				slide.setStyle('display', 'none')

			})
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

Brickrouge.Widget.Carousel = new Class({

	Extends: Brickrouge.Carousel

})