/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define('Brickrouge.Form', [

	'./Core',
	'olvlvl-subject'

],

/**
 * @param {Brickrouge} Brickrouge
 * @param {Brickrouge.Subject} Subject
 *
 * @returns {Brickrouge.Form}
 */
(Brickrouge, Subject) => {

	"use strict";

	/**
	 * Key for generic errors
	 */
	const GENERIC = '__generic__'

	/**
	 * Fired before the form is submitted.
	 *
	 * @event Brickrouge.Form#submit
	 */
	const SubmitEvent = Subject.createEvent(function () {

	})

	/**
	 * Fired before the XHR is sent
	 *
	 * @event Brickrouge.Form#request
	 */
	const RequestEvent = Subject.createEvent(function () {

	})

	/**
	 * Fired when the request is successful
	 *
	 * @param {Object} response
	 *
	 * @event Brickrouge.Form#success
	 * @property {Object} response
	 */
	const SuccessEvent = Subject.createEvent(function (response) {

		this.response = response

	})

	/**
	 * Fired when the request failed
	 *
	 * @param {XMLHttpRequest} xhr
	 * @param {Object} response
	 *
	 * @event Brickrouge.Form#failure
	 * @property {XMLHttpRequest} xhr
	 * @property {Object} response
	 */
	const FailureEvent = Subject.createEvent(function (xhr, response) {

		this.xhr = xhr
		this.response = response

	})

	/**
	 * Fired when the request is complete
	 *
	 * @event Brickrouge.Form#complete
	 */
	const CompleteEvent = Subject.createEvent(function () {

	})

	/**
	 * @type {Brickrouge.Form.Options|*}
	 */
	const DEFAULT_OPTIONS = {

		url: null,
		useXHR: false,
		replaceOnSuccess: false

	}

	/**
	 * @param {string} type
	 *
	 * @returns {Element}
	 */
	function createAlertElement(type)
	{
		const close = document.createElement('button')

		close.type = 'button'
		close.innerHTML = '×'
		close.className = 'close'
		close.setAttribute('data-dismiss', 'alert')

		const element = document.createElement('div')

		element.className = `alert alert-${type} dismissible`
		element.appendChild(close)

		return element
	}

	/**
	 * Forms created.
	 *
	 * @type {Array<int, Brickrouge.Form>}
	 */
	const forms = []

	/**
	 * Support for asynchronous forms.
	 *
	 * @property {HTMLFormElement} element
	 */
	class Form extends Brickrouge.mixin(Object, Subject) {

		/**
		 * @returns {Brickrouge.Form.SubmitEvent}
		 * @constructor
		 */
		static get SubmitEvent()
		{
			return SubmitEvent
		}

		/**
		 * @returns {Brickrouge.Form.RequestEvent}
		 * @constructor
		 */
		static get RequestEvent()
		{
			return RequestEvent
		}

		/**
		 * @returns {Brickrouge.Form.SuccessEvent}
		 * @constructor
		 */
		static get SuccessEvent()
		{
			return SuccessEvent
		}

		/**
		 * @returns {Brickrouge.Form.FailureEvent}
		 * @constructor
		 */
		static get FailureEvent()
		{
			return FailureEvent
		}

		/**
		 * @returns {Brickrouge.Form.CompleteEvent}
		 * @constructor
		 */
		static get CompleteEvent()
		{
			return CompleteEvent
		}

		/**
		 * Retrieve the Brickrouge form associated with an element.
		 *
		 * @param {HTMLFormElement} element
		 *
		 * @returns {Brickrouge.Form}
		 */
		static from(element)
		{
			const uid = Brickrouge.uidOf(element)

			if (uid in forms)
			{
				return forms[uid]
			}

			throw new Error("No Brickrouge form is associated with this element")
		}

		/**
		 * @param {HTMLFormElement} element
		 * @param {Brickrouge.Form.Options} options
		 */
		constructor(element, options)
		{
			super()

			this.element = element
			this.options = Object.assign({}, DEFAULT_OPTIONS, options)

			forms[Brickrouge.uidOf(element)] = this

			element.addEventListener('submit', ev => {

				if (!this.isProcessingSubmit) {
					return
				}

				ev.preventDefault()

				this.submit()

			})
		}

		/**
		 * Determine whether the submit event should be processed by the class.
		 *
		 * @returns bool
		 */
		get isProcessingSubmit()
		{
			let options = this.options

			return options.useXHR
			|| options.onRequest
			|| options.onComplete
			|| options.onFailure
			|| options.onSuccess
			|| options.replaceOnSuccess
		}

		/**
		 * @param {string|Object} messages
		 * @param {string} type
		 */
		alert(messages, type)
		{
			let alert = this.element.querySelector('.alert-' + type) || createAlertElement(type)

			this.normalizeMessages(messages).forEach(message => {

				const p = document.createElement('p')

				p.innerHTML = message

				alert.appendChild(p)

			})

			this.insertAlert(alert)
		}

		/**
		 * @param messages
		 *
		 * @returns {Array<string>}
		 */
		normalizeMessages(messages)
		{
			if (typeof messages === 'string')
			{
				return [ messages ]
			}

			if (typeof messages === 'object')
			{
				const array = []

				Object.forEach(messages, (message, id) => {

					if (id !== GENERIC)
					{
						this.addError(id)
					}

					if (!message || message === true)
					{
						return
					}

					array.push(message)

				})

				return array
			}

			throw new Error("Unable to normalize messages: " + JSON.stringify(messages))
		}

		/**
		 * @param {Element} alert
		 */
		insertAlert(alert)
		{
			const el = this.element

			if (alert.classList.contains('alert-success') && this.options.replaceOnSuccess)
			{
				alert.querySelector('[data-dismiss="alert"]').remove()
				alert.classList.add('dismissible')

				el.parentNode.insertBefore(alert, el)
				el.classList.add('hidden')
			}
			else if (!alert.parentNode)
			{
				el.insertBefore(alert, el.firstChild)
			}
		}

		/**
		 * Clears dismissible alerts and removes `.has-danger`.
		 */
		clearAlert()
		{
			const form = this.element

			form.querySelectorAll('.alert.dismissible').forEach(alert => {

				alert.remove()

			})

			form.querySelectorAll('.has-danger').forEach(control => {

				control.classList.remove('has-danger')

			})
		}

		/**
		 * Add a control error.
		 *
		 * @param {string} name Name of the control
		 */
		addError(name)
		{
			const control = this.element.elements[name]

			if (!control)
			{
				return
			}

			const group = control.closest('.form-group')

			if (group)
			{
				group.classList.add('has-danger')
			}

			if (typeOf(control) == 'collection')
			{
				let parent = control[0].closest('.radio-group')

				if (parent)
				{
					parent.classList.add('has-danger')
				}
				else
				{
					control.forEach(checkbox => {

						checkbox.classList.add('has-danger')

					})
				}

				return
			}

			control.classList.add('has-danger')
		}

		submit()
		{
			this.notify(new SubmitEvent)
			this.getOperation().send(this.element)
		}

		getOperation()
		{
			if (this.operation)
			{
				return this.operation
			}

			return this.operation = new Request.JSON({

				url: this.options.url || this.element.action,
				method: this.element.getAttribute('method') || 'GET',

				onRequest: this.request.bind(this),
				onComplete: this.complete.bind(this),
				onSuccess: this.success.bind(this),
				onFailure: this.failure.bind(this)

			})
		}

		/**
		 * @fires Brickrouge.Form#request
		 */
		request()
		{
			this.clearAlert()
			this.notify(new RequestEvent)
		}

		/**
		 * @fires Brickrouge.Form#complete
		 */
		complete()
		{
			this.notify(new CompleteEvent)
		}

		/**
		 * @fires Brickrouge.Form#success
		 */
		success(response)
		{
			if (response.message)
			{
				this.alert(response.message, 'success')
			}

			this.notify(new SuccessEvent(response)).notify(new CompleteEvent)
		}

		/**
		 * @fires Brickrouge.Form#failure
		 */
		failure(xhr)
		{
			let response = {}

			try
			{
				response = JSON.parse(xhr.responseText)
			}
			catch (e)
			{
				if (console)
				{
					console.error(e)
				}

				alert(xhr.statusText)
			}

			if (response.errors)
			{
				this.alert(response.errors, 'danger')
			}

			if (response.exception)
			{
				alert(response.exception)
			}

			this.notify(new FailureEvent(xhr, response))
		}

		/**
		 * @param {Function} callback
		 */
		observeSubmit(callback)
		{
			this.observe(SubmitEvent, callback)
		}

		/**
		 * @param {Function} callback
		 */
		observeRequest(callback)
		{
			this.observe(RequestEvent, callback)
		}

		/**
		 * @param {Function} callback
		 */
		observeSuccess(callback)
		{
			this.observe(SuccessEvent, callback)
		}

		/**
		 * @param {Function} callback
		 */
		observeFailure(callback)
		{
			this.observe(FailureEvent, callback)
		}

		/**
		 * @param {Function} callback
		 */
		observeComplete(callback)
		{
			this.observe(CompleteEvent, callback)
		}
	}

	return Brickrouge.Form = Form

})
