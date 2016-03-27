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

	var forms = []

	/**
	 * Retrieve the Brickrouge form associated with an element.
	 *
	 * @param {Element} element
	 *
	 * @returns {Form}
	 */
	function from(element)
	{
		var uid = Brickrouge.uidOf(element)

		if (uid in forms)
		{
			return forms[uid]
		}

		throw new Error("No Brickrouge form is associated with this element")
	}

	/**
	 * Fired before the form is submitted.
	 */
	const EVENT_SUBMIT = 'submit'

	/**
	 * Fired before the XHR is sent
	 */
	const EVENT_REQUEST = 'request'

	/**
	 * Fired when the request is complete
	 */
	const EVENT_COMPLETE = 'complete'

	/**
	 * Fired when the request is successful
	 */
	const EVENT_SUCCESS = 'success'

	/**
	 * Fired when the request failed
	 */
	const EVENT_FAILURE = 'failure'

	/**
	 * Key for generic errors
	 */
	const GENERIC = '__generic__'

	var forms = []

	/**
	 * Retrieve the Brickrouge form associated with an element.
	 *
	 * @param {Element} element
	 *
	 * @returns {Form}
	 */
	function from(element)
	{
		var uid = Brickrouge.uidOf(element)

		if (uid in forms)
		{
			return forms[uid]
		}

		throw new Error("No Brickrouge form is associated with this element")
	}

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

			forms[Brickrouge.uidOf(el)] = this

			el.addEventListener(EVENT_SUBMIT, function(ev) {

				if (!this.isProcessingSubmit) return

				ev.preventDefault()

				this.submit()

			}.bind(this))
		},

		/**
		 * Determine whether the submit event should be processed by the class.
		 *
		 * @returns bool
		 */
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
			|| new Element('.alert.alert-' + type + '.dismissible').adopt(new Element('button.close[type="button"][data-dismiss="alert"]', { html: '×' }))

			if (typeOf(messages) == 'string')
			{
				messages = [ messages ]
			}
			else if (typeOf(messages) == 'object')
			{
				messages = []

				Object.forEach(original, function(message, id) {

					if (id !== GENERIC)
					{
						this.addError(id)
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
			else if (!alert.parentNode)
			{
				alert.inject(el, 'top')
			}
		},

		/**
		 * Clears dismissible alerts and removes `.has-danger`.
		 */
		clearAlert: function()
		{
			var form = this.element

			form.querySelectorAll('.alert.dismissible').forEach(function (alert) {

				alert.remove()

			})

			form.querySelectorAll('.has-danger').forEach(function (control) {

				control.classList.remove('has-danger')

			})
		},

		/**
		 * Add a control error.
		 *
		 * @param {string} name Name of the control
		 */
		addError: function (name) {

			var parent
			, group
			, control = this.element.elements[name]

			if (!control) return

			group = control.closest('.form-group')

			if (group)
			{
				group.classList.add('has-danger')
			}

			if (typeOf(control) == 'collection')
			{
				parent = control[0].closest('.radio-group')

				if (parent)
				{
					parent.classList.add('has-danger')
				}
				else
				{
					control.forEach(function (checkbox) {

						checkbox.classList.add('has-danger')

					})
				}

				return
			}

			control.classList.add('has-danger')

		},

		submit: function()
		{
			this.fireEvent(EVENT_SUBMIT, {})
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
			this.fireEvent(EVENT_REQUEST, arguments)
		},

		complete: function()
		{
			this.fireEvent(EVENT_COMPLETE, arguments)
		},

		success: function(response)
		{
			if (response.message)
			{
				this.alert(response.message, 'success')
			}

			this.fireEvent(EVENT_SUCCESS, arguments).fireEvent(EVENT_COMPLETE, arguments)
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
					console.error(e)
				}

				alert(xhr.statusText)
			}

			this.fireEvent(EVENT_FAILURE, [ xhr, response ])
		}
	})

	Form.from = from
	Form.EVENT_SUBMIT = EVENT_SUBMIT
	Form.EVENT_REQUEST = EVENT_REQUEST
	Form.EVENT_COMPLETE = EVENT_COMPLETE
	Form.EVENT_SUCCESS = EVENT_SUCCESS
	Form.EVENT_FAILURE = EVENT_FAILURE
	Form.STORED_KEY_NAME = '_brickrouge_form_key'
	Form.from = from

	return Brickrouge.Form = Form

})
