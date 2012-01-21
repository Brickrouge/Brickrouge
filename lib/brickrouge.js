/*!
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

var BrickRouge = {

	Utils: {

		Busy: new Class({

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

	Widget: {

	},

	Form: new Class({

		Implements: [ Options, Events ],

		options:
		{
			url: null,
			useXHR: false
		},

		initialize: function(el, options)
		{
			this.element = document.id(el);
			this.setOptions(options);

			if (this.options.useXHR || (options && (options.onRequest || options.onComplete || options.onFailure || options.onSuccess)))
			{
				this.element.addEvent
				(
					'submit', function(ev)
					{
						ev.stop();

						this.submit();
					}
					.bind(this)
				);
			}
		},

		alert: function(messages, type)
		{
			var original, alert = this.element.getElement('div.alert-message.' + type) || new Element('div.alert-message.' + type, { html: '<a href="#close" class="close">×</a>'});

			if (typeOf(messages) == 'string')
			{
				messages = [ messages ];
			}
			else if (typeOf(messages) == 'object')
			{
				original = messages;

				messages = [];

				Object.each
				(
					original, function(message, id)
					{
						if (typeOf(id) == 'string' && id != '_base')
						{
							var parent, field, el = this.element.elements[id], i;

							if (typeOf(el) == 'collection')
							{
								parent = el[0].getParent('div.radio-group');
								field = parent.getParent('.field');

								if (parent)
								{
									parent.addClass('error');
								}
								else
								{
									for (i = 0, j = el.length ; i < j ; i++)
									{
										el[i].addClass('error');
									}
								}
							}
							else
							{
								el.addClass('error');
								field = el.getParent('.field');
							}

							if (field)
							{
								field.addClass('error');
							}
						}

						if (!message || message === true)
						{
							return;
						}

						messages.push(message);
					},

					this
				);
			}

			if (!messages.length)
			{
				return;
			}

			messages.each
			(
				function(message)
				{
					alert.adopt(new Element('p', { html: message }));
				}
			);

			if (!alert.parentNode)
			{
				alert.inject(this.element, 'top');
			}
		},

		clearAlert: function()
		{
			var alerts = this.element.getElements('div.alert-message');

			if (alerts)
			{
				alerts.destroy();
			}

			this.element.getElements('.error').removeClass('error');
		},

		submit: function()
		{
			this.fireEvent('submit', {});
			this.getOperation().send(this.element);
		},

		getOperation: function()
		{
			if (this.operation)
			{
				return this.operation;
			}

			return this.operation = new Request.JSON
			({
				url: this.options.url || this.element.action,

				onRequest: this.request.bind(this),
				onComplete: this.complete.bind(this),
				onSuccess: this.success.bind(this),
				onFailure: this.failure.bind(this)
			});
		},

		request: function()
		{
			this.clearAlert();
			this.fireEvent('request', arguments);
		},

		complete: function()
		{
			this.fireEvent('complete', arguments);
		},

		success: function(response)
		{
			if (response.success)
			{
				this.alert(response.success, 'success');
			}

			this.onSuccess(response);
		},

		onSuccess: function(response)
		{
			this.fireEvent('complete', arguments).fireEvent('success', arguments).callChain();
		},

		failure: function(xhr)
		{
			var response = JSON.decode(xhr.responseText);

			if (response && response.errors)
			{
				this.alert(response.errors, 'error');
			}

			this.fireEvent('failure', arguments);
		}
	}),

	/**
	 * Update the document by adding missing CSS and JS assets.
	 *
	 * @param object assets
	 * @param function done
	 */
	updateAssets: (function()
	{
		var available_css=null, available_js=null;

		return function (assets, done)
		{
			var css=new Array(), js=new Array(), js_count;

			if (available_css === null)
			{
				available_css = new Array();

				if (typeof(document_cached_css_assets) !== 'undefined')
				{
					available_css = document_cached_css_assets;
				}

				document.id(document.head).getElements('link[type="text/css"]').each
				(
					function(el)
					{
						available_css.push(el.get('href'));
					}
				);
			}

			if (available_js === null)
			{
				available_js = new Array();

				if (typeof(brickrouge_cached_js_assets) !== 'undefined')
				{
					available_js = brickrouge_cached_js_assets;
				}

				document.id(document.html).getElements('script').each
				(
					function(el)
					{
						var src = el.get('src');

						if (src) available_js.push(src);
					}
				);
			}

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

			js_count = js.length;

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

	}) (),

	/**
	 * Awakes sleeping widgets.
	 *
	 * Constructors defined under the `Widget` namespace are traversed and for each one of them
	 * matching widgets are searched in the DOM a new widget is created using the constructor.
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
		container = container || document.id(document.body);

		Object.each
		(
			BrickRouge.Widget,
			(
				function(constructor, key)
				{
					var cl = '.widget' + key.hyphenate();

					container.getElements(cl).each
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
	}
};

/*
 * The Request.Element class required the Request.API class provided by the ICanBoogie framework,
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
		var widget = this.retrieve('widget'), type, constructorName, constructor;

		if (!widget)
		{
			type = this.className.match(/widget(-\S+)/);

			if (type && type.length)
			{
				constructorName = type[1].camelCase();
				constructor = BrickRouge.Widget[constructorName];

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

/**
 * Returns the dataset of the element.
 *
 * The dataset is created by readding and aggregatting value defined by the data-* attributes.
 */
Element.Properties.dataset = {

	get: function() {

		var dataset = {}, attributes = this.attributes, i, attr;

		for (i = 0, y = attributes.length ; i < y ; i++)
		{
			attr = attributes[i];

			if (!attr.name.match(/^data-/))
			{
				continue;
			}

			dataset[attr.name.substring(5).camelCase()] = attr.value;
		}

		return dataset;
	}
};

/**
 * Calls the BrickRouge.awakeWidgets when the `elementsready` event is fired on the document.
 */
document.addEvent
(
	'elementsready', function(ev)
	{
		BrickRouge.awakeWidgets(ev.target);
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
		document.fireEvent('elementsready', { target: document.id(document.body) });
	}
);

/**
 * Destroy the alert message when its close icon is clicked. The "error" class is also removed from
 * elements.
 */
document.id(document.body).addEvent('click:relay(div.alert-message a.close)', function(ev, target){

	ev.stop();

	var form = target.getParent('form');

	if (form) {

		form.getElements('.error').removeClass('error');
	}

	target.getParent('div.alert-message').destroy();
});
