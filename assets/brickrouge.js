/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var BrickRouge = {

	Utils: {

		Busy: new Class ({

			startBusy: function()
			{
				if (++this.busyNest == 1)
				{
					return;
				}

				this.element.addClass('busy');
			},

			finishBusy: function()
			{
				if (--this.busyNest)
				{
					return;
				}

				this.element.removeClass('busy');
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
		var available_css;
		var available_js;

		return function (assets, done)
		{
			if (available_css === undefined)
			{
				available_css = [];

				if (typeof(document_cached_css_assets) !== 'undefined')
				{
					available_css = document_cached_css_assets;
				}

				$(document.head).getElements('link[type="text/css"]').each
				(
					function(el)
					{
						available_css.push(el.get('href'));
					}
				);
			}

			if (available_js === undefined)
			{
				available_js = [];

				if (typeof(document_cached_js_assets) !== 'undefined')
				{
					available_js = document_cached_js_assets;
				}

				$(document.html).getElements('script').each
				(
					function(el)
					{
						var src = el.get('src');

						if (src) available_js.push(src);
					}
				);
			}

			var css = [];

			assets.css.each
			(
				function(url)
				{
					if (available_css.indexOf(url) != -1)
					{
						return;
					}

					css.push(url);
				}
			);

			css.each
			(
				function(url)
				{
					new Asset.css(url);

					available_css.push(url);
				}
			);

			var js = [];

			assets.js.each
			(
				function(url)
				{
					if (available_js.indexOf(url) != -1)
					{
						return;
					}

					js.push(url);
				}
			);

			var js_count = js.length;

			if (!js_count)
			{
				done();

				return;
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
								available_js.push(url);

								if (!--js_count)
								{
									done();
								}
							}
						}
					);
				}
			);
		};

	}) ()
};

/**
 * Extends Request.API to support the loading of single HTML elements.
 */
Request.Element = new Class
({
	Extends: Request.API,

	onSuccess: function(response, text)
	{
		var el = Elements.from(response.rc).shift();

		if (!response.assets)
		{
			this.parent(el, response, text);

			return;
		}

		BrickRouge.updateAssets
		(
			response.assets, function()
			{
				this.fireEvent('complete', [ response, text ]).fireEvent('success', [ el, response, text ]).callChain();
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
			options = {};
		}

		options.url = 'widgets/' + cl;
		options.onSuccess = onSuccess;

		this.parent(options);
	}
});

/**
 * This is the namespace for all widgets constructors.
 */
var Widget = {};

Element.Properties.widget = {

	get: function()
	{
		var widget = this.retrieve('widget');

		if (!widget)
		{
			var type = this.className.match(/widget(-\S+)/);

			if (type && type.length)
			{
				var constructorName = type[1].camelCase();
				var constructor = Widget[constructorName];

				if (!constructor)
				{
					throw "Constructor \"" + constructor + "\"is not defined to create widgets of type \"" + type + "\"";
				}

				widget = new constructor(this, this.get('dataset'));

				this.store('widget', widget);
			}
		}

		return widget;
	}
};

Widget.Searchbox = new Class
({
	Implements: BrickRouge.Utils.Busy,

	initialize: function(el, options)
	{
		this.element = $(el);
	}
});

/**
 * Widgets auto-constructor.
 *
 * On the 'elementsready' document event, constructors defined under the `Widget` namespace are
 * traversed and for each one of them, matching widgets are searched in the DOM and if the `widget`
 * property is not stored, a new widget is created using the constructor.
 *
 * Widgets are matched against a constructor based on the following naming convention: for a
 * "AdjustNode" constructor, the elements matching ".widget-adjust-node" are turned into widgets.
 */
document.addEvent
(
	'elementsready', function()
	{
		Object.each
		(
			Widget,
			(
				function(constructor, key)
				{
					var cl = '.widget' + key.hyphenate();

					$$(cl).each
					(
						function(el)
						{
							if (el.retrieve('widget'))
							{
								return;
							}

							var widget = new constructor(el, el.get('dataset'));

							el.store('widget', widget);
						}
					);
				}
			)
		);

		/*
		 * Hide alert messages when the close button is pressed.
		 */

		$$('div.alert-message a.close').addEvent
		(
			'click', function(ev)
			{
				ev.stop();

				var slide = new Fx.Slide(this.getParent(), { duration: 'short' });

				slide.slideOut().chain
				(
					function()
					{
						this.wrapper.destroy();

						delete slide;
					}
				);
			}
		);
	}
);

/**
 * The "elementsready" event is fired for elements to be initialized, to become alive thanks to the
 * magic of Javascript. This event is usually fired when new widgets are added to the DOM.
 */
window.addEvent
(
	'domready', function()
	{
		document.fireEvent('elementsready', { target: $(document.body) });
	}
);