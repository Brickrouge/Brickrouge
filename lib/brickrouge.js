/*!
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

!function() {

	var IS_ATTRIBUTE = 'brickrouge-is'
	, WIDGET_SELECTOR = '[' + IS_ATTRIBUTE + ']'

	/**
	 * Construct the widget associated with an element.
	 *
	 * The `brickrouge.widget` event is fired on the `window` object when a widget is constructed.
	 * The event is fired with the widget and its element as arguments. If an error occurs while
	 * the event is processed it is caught and logged to the console as an error.
	 *
	 *
	 * @param element The element
	 */
	function constructWidget(element)
	{
		var constructorName = element.get(IS_ATTRIBUTE)

		if (!constructorName)
		{
			throw new Error("The " + IS_ATTRIBUTE + " attribute is not defined.")
		}

		var constructor = Brickrouge.Widget[constructorName]

		if (!constructor)
		{
			throw new Error("Undefined constructor: " + constructorName)
		}

		element.store('widget', true)

		var widget = new constructor(element, element.get('dataset'))

		element.store('widget', widget)

		try
		{
			window.fireEvent('brickrouge.widget', [ widget, this ])
		}
		catch (e)
		{
			console.error(e)
		}

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
			}

			return widget
		}
	}

	/**
	 * Construct widgets.
	 *
	 * Widgets are constructed by creating a new object using a constructor, an element and
	 * options. The constructor name is defined by the `IS_ATTRIBUTE` attribute of the
	 * element, and the dataset of the element is used as options.
	 *
	 * Elements are collected using the {@link WIDGET_SELECTOR} selector from the
	 * deepest nodes to the root. The function uses the custom `widget` property to inderectly
	 * create the widgets.
	 *
	 * @param element This optional parameter can be used to limit widget construction to a
	 * specified element. If the element if not defined or is empty the document body is used
	 * instead.
	 */
	function constructWidgets(element)
	{
		element = element || document.body

		var elements = element.getElements(WIDGET_SELECTOR)

		if (element.match(WIDGET_SELECTOR))
		{
			elements.unshift(element)
		}

		elements.reverse().each(function(el) {

			if (el.retrieve('widget')) return

			constructWidget(el)
		})
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

		IS_ATTRIBUTE: IS_ATTRIBUTE,
		WIDGET_SELECTOR: WIDGET_SELECTOR,

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

			window.fireEvent('brickrouge.update', el)

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

})
