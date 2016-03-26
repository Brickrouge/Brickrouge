/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

	'./Core'

], function (Brickrouge) {
	"use strict";

	/**
	 * Support for asynchronous forms.
	 */
	var Form = new Class({

		Implements: [ Options, Events ],

		options:
		{
			url: null,
			useXHR: false,
			replaceOnSuccess: false
		},

		initialize: function(el, options)
		{
			this.element = el
			this.setOptions(options)

			el.store('brickrouge.form', this)

			el.addEvent('submit', (ev) => {

				if (!this.isProcessingSubmit) return

				ev.preventDefault()

				this.submit()

			})
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
			, alert = this.element.querySelector('.alert-' + type)
			|| new Element('div.alert.alert-' + type).adopt(new Element('button.close[type="button"][data-dismiss="alert"]', { html: '×' }))

			if (typeOf(messages) == 'string')
			{
				messages = [ messages ]
			}
			else if (typeOf(messages) == 'object')
			{
				messages = []

				Object.forEach(original, function(message, id) {

					if (typeOf(id) == 'string' && id != '_base')
					{
						var parent
						, field = null
						, el = document.id(this.element.elements[id])
						, i
						, j

						if (typeOf(el) == 'collection')
						{
							parent = document.id(el[0]).closest('div.radio-group')
							field = parent.closest('.form-group')

							if (parent)
							{
								parent.classList.add('has-danger')
							}
							else
							{
								for (i = 0, j = el.length ; i < j ; i++)
								{
									document.id(el[i]).classList.add('has-danger')
								}
							}
						}
						else if (el)
						{
							el.classList.add('has-danger')
							field = el.closest('.control-group')
						}

						if (field)
						{
							field.classList.add('has-danger')
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

			messages.forEach(function(message) {

				alert.appendChild(new Element('p', { html: message }))

			})

			this.insertAlert(alert)
		},

		insertAlert: function(alert)
		{
			var el = this.element

			if (alert.classList.contains('alert-success') && this.options.replaceOnSuccess)
			{
				alert.querySelector('[data-dismiss="alert"]').remove()
				alert.classList.add('dismissible')
				alert.inject(el, 'before')

				el.classList.add('hidden')
			}
			else if (!alert.closest())
			{
				alert.inject(el, 'top')
			}
		},

		clearAlert: function()
		{
			var el = this.element

			el.querySelectorAll('.alert.dismissible').forEach(function (alert) {

				alert.remove()

			})

			el.querySelectorAll('.has-danger').forEach(function (control) {

				control.classList.remove('has-danger')

			})
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

	Form.STORED_KEY_NAME = '_brickrouge_form_key'

	Brickrouge.Form = Form

	return Form

})
