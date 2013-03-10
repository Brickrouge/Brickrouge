/*
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

Brickrouge.Form.STORED_KEY_NAME = '_brickrouge_form_key'