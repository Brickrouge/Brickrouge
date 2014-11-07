Brickrouge.Modal = new Class({

	Implements: [ Options, Events ],

	options: {

		/*onAction: function() {} */

	},

	initialize: function(el, options)
	{
		this.element = el = document.id(el)

		el.addEvent('click', function(ev) {

			if (ev.target != el) return

			this.hide(this)

		}.bind(this))

		this.setOptions(options)

		el.store('modal', this)

		el.addEvent('click:relay([data-action])', function(ev, el) {

			this.action(el.get('data-action'))

		}.bind(this))
	},

	show: function()
	{
		var el = this.element

		el.addClass('in')
		el.removeClass('out')
		el.removeClass('hide')

		window.fireEvent('brickrouge.modal.show', this)
	},

	hide: function()
	{
		var el = this.element

		window.fireEvent('brickrouge.modal.hide', this)

		el.removeClass('in')
		el.addClass('out')
		el.addClass('hide')
	},

	isHidden: function()
	{
		return this.element.hasClass('hide')
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

window.addEvent('click:relay([data-toggle="modal"])', function(ev, el) {

	if (ev.rightClick) return

	var modalId = el.get('href').substring(1)
	, modalEl = document.id(modalId)
	, modal

	if (!modalEl) return

	ev.stop()

	modal = modalEl.retrieve('modal')

	if (!modal)
	{
		modal = new Brickrouge.Modal(modalEl)
		modalEl.store('modal', modal)
	}

	modal.toggle()
})

window.addEvent('click:relay([data-dismiss="modal"])', function(ev, el) {

	if (ev.rightClick) return

	var modalEl = el.getParent('.modal')
	, modal

	if (!modalEl) return

	ev.stop()

	modal = modalEl.retrieve('modal')

	if (modal)
	{
		modal.hide()
	}
	else
	{
		modalEl.addClass('hide')
	}

});
