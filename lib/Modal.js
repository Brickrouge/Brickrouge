define([

	'./Core',
	'olvlvl-subject'

],

/**
 *
 * @param {Brickrouge} Brickrouge
 * @param {Brickrouge.Subject} Subject
 *
 * @returns {Brickrouge.Modal}
 */
function (Brickrouge, Subject) {

	"use strict";

	/**
	 * @param {Modal} modal
	 *
	 * @event Brickrouge#modal:show
	 * @property {Modal} modal
	 */
	const ShowEvent = Subject.createEvent(function (modal) {

		this.modal = modal

	})

	/**
	 * @param {Modal} modal
	 *
	 * @event Brickrouge#modal:hide
	 * @property {Modal} modal
	 */
	const HideEvent = Subject.createEvent(function (modal) {

		this.modal = modal

	})

	/**
	 * @param {string} action
	 *
	 * @event Brickrouge.Modal#action
	 * @property {string} action
	 */
	const ActionEvent = Subject.createEvent(function (action) {

		this.action = action

	})

	const instances = []

	class Modal extends Brickrouge.mixin(Object, Subject)
	{
		/**
		 * Returns the modal associated with an element.
		 *
		 * @param {Element} element
		 *
		 * @returns {Modal}
		 */
		static from(element)
		{
			const uid = Brickrouge.uidOf(element)

			if (uid in instances)
			{
				return instances[uid]
			}

			return instances[uid] = new Modal(element)
		}

		/**
		 * @returns {ActionEvent}
		 * @constructor
		 */
		static get ActionEvent()
		{
			return ActionEvent
		}

		/**
		 * @returns {ShowEvent}
		 * @constructor
		 */
		static get ShowEvent()
		{
			return ShowEvent
		}

		/**
		 * @returns {HideEvent}
		 * @constructor
		 */
		static get HideEvent()
		{
			return HideEvent
		}

		/**
		 * @param {Element} el
		 * @param {Object} [options]
		 */
		constructor(el, options)
		{
			super()

			this.element = el
			this.options = Object.assign({}, options)

			el.addEventListener('click', ev => {

				if (ev.target != el)
				{
					return
				}

				this.hide(this)

			})

			instances[Brickrouge.uidOf(el)] = this

			el.addDelegatedEventListener('[data-action]', 'click', (ev, el) => {

				this.action(el.get('data-action'))

			})
		}

		show()
		{
			const el = this.element

			el.classList.add('in')
			el.classList.remove('out')
			el.classList.remove('hide')

			Brickrouge.notify(new ShowEvent(this))
		}

		hide()
		{
			const el = this.element

			Brickrouge.notify(new HideEvent(this))

			el.classList.remove('in')
			el.classList.add('out')
			el.classList.add('hide')
		}

		/**
		 * @returns {boolean}
		 */
		isHidden()
		{
			return this.element.classList.contains('hide')
		}

		toggle()
		{
			this.isHidden() ? this.show() : this.hide()
		}

		action(action)
		{
			this.notify(new ActionEvent(action))
		}
	}

	return Brickrouge.Modal = Modal

})
