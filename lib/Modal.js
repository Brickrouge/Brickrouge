define([

	'./Core'

], function (Brickrouge) {

	"use strict";

	let instances = []

	let Modal = new Class({

		Implements: [ Options, Events ],

		options: {

			/*onAction: function() {} */

		},

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

			Brickrouge.notify('modal:show', [ this ])
		},

		hide: function()
		{
			let el = this.element

			Brickrouge.notify('modal:hide', [ this ])

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
			this.fireEvent('action', action)
		}
	})

	Modal.from = function (element) {

		let uid = Brickrouge.uidOf(element)

		if (uid in instances)
		{
			return instances[uid]
		}

		return instances[uid] = new Modal(element)

	}

	Brickrouge.Modal = Modal

	return Modal

})
