define([

	'./Core',
	'../node_modules/brickrouge/lib/subject.js'

], function (Brickrouge, Subject) {

	"use strict";

	/**
	 * @param {Modal} modal
	 *
	 * @event Brickrouge#modal:show
	 * @property {Modal} modal
	 */
	let ShowEvent = Subject.createEvent('modal:show', function (modal) {

		this.modal = modal

	})

	/**
	 * @param {Modal} modal
	 *
	 * @event Brickrouge#modal:hide
	 * @property {Modal} modal
	 */
	let HideEvent = Subject.createEvent('modal:hide', function (modal) {

		this.modal = modal

	})

	/**
	 * @param {string} action
	 *
	 * @event Brickrouge.Modal#action
	 * @property {string} action
	 */
	let ActionEvent = Subject.createEvent('action', function (action) {

		this.action = action

	})

	let Modal = new Class({

		Implements: [ Options, Subject ],

		initialize: function(el, options)
		{
			this.element = el

			el.addEventListener('click', (ev) => {

				if (ev.target != el) return

				this.hide(this)

			})

			this.setOptions(options)

			instances[Brickrouge.uidOf(el)] = this

			el.addDelegatedEventListener('[data-action]', 'click', (ev, el) => {

				this.action(el.get('data-action'))

			})
		},

		show: function()
		{
			let el = this.element

			el.classList.add('in')
			el.classList.remove('out')
			el.classList.remove('hide')

			Brickrouge.notify(new ShowEvent(this))
		},

		hide: function()
		{
			let el = this.element

			Brickrouge.notify(new HideEvent(this))

			el.classList.remove('in')
			el.classList.add('out')
			el.classList.add('hide')
		},

		isHidden: function()
		{
			return this.element.classList.contains('hide')
		},

		toggle: function()
		{
			this.isHidden() ? this.show() : this.hide()
		},

		action: function(action)
		{
			this.notify(new ActionEvent(action))
		}
	})

	let instances = []

	/**
	 * Returns the modal associated with an element.
	 *
	 * @param {Element} element
	 *
	 * @returns {Modal}
	 */
	function from(element) {

		let uid = Brickrouge.uidOf(element)

		if (uid in instances)
		{
			return instances[uid]
		}

		return instances[uid] = new Modal(element)

	}

	Object.defineProperties(Modal, {

		EVENT_ACTION: { value: ActionEvent },
		EVENT_SHOW:   { value: ShowEvent },
		EVENT_HIDE:   { value: HideEvent },
		from:         { value: from }

	})

	return Brickrouge.Modal = Modal

})
