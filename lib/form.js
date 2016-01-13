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
		this.element = el = document.id(el)
		this.setOptions(options)

		el.store('birckrouge.form', this)

		el.addEvent('submit', function(ev) {

			if (!this.isProcessingSubmit) return

			ev.preventDefault()

			this.submit()

		}.bind(this))
	},

	isProcessingSubmit: function()
	{
		var options = this.options

		return options.useXHR
		|| options.onRequest
		|| options.onComplete
		|| options.onFailure
		|| options.onSuccess
		|| options.replaceOnSuccess
	},

	alert: function(messages, type)
	{
		var original = messages
		, alert = this.element.getElement('div.alert-' + type)
		|| new Element('div.alert.alert-' + type).adopt(new Element('button.close[type="button"][data-dismiss="alert"]', { html: '×' }))

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
					, j

					if (typeOf(el) == 'collection')
					{
						parent = document.id(el[0]).getParent('div.radio-group')
						field = parent.getParent('.control-group')

						if (parent)
						{
							parent.addClass('danger')
						}
						else
						{
							for (i = 0, j = el.length ; i < j ; i++)
							{
								document.id(el[i]).addClass('danger')
							}
						}
					}
					else if (el)
					{
						el.addClass('danger')
						field = el.getParent('.control-group')
					}

					if (field)
					{
						field.addClass('danger')
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
		var el = this.element

		if (alert.hasClass('alert-success') && this.options.replaceOnSuccess)
		{
			alert.getElement('[data-dismiss="alert"]').dispose()
			alert.addClass('undismissable')
			alert.inject(el, 'before')

			el.addClass('hidden')
		}
		else if (!alert.getParent())
		{
			alert.inject(el, 'top')
		}
	},

	clearAlert: function()
	{
		var el = this.element
		, alerts = el.getElements('div.alert:not(.undismissable)')

		if (alerts)
		{
			alerts.destroy()
		}

		el.getElements('.danger').removeClass('danger')
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

		this.fireEvent('success', arguments).fireEvent('complete', arguments)
	},

	failure: function(xhr)
	{
		var response = {}

		try
		{
			response = JSON.decode(xhr.responseText)

			if (response.errors)
			{
				this.alert(response.errors, 'danger')
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
