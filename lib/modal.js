Brickrouge.Modal = new Class({

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.backdrop = new Element('div.modal-backdrop')
		this.backdrop.addEvent('click', this.hide.bind(this))

		/*
		this.header = new Element('div.modal-header').adopt
		([
			new Element('button.close[data-dismiss="modal"][aria-hidden="true"]', { html: "&times;"}),
			new Element('h3', { html: 'Title' })
		])

		this.body = new Element('div.modal-body').adopt(el)
		this.footer = new Element('div.modal-footer')

		this.modal = new Element('div.modal.hide.fade').adopt([ this.header, this.body, this.footer ])
		*/
	},

	show: function()
	{
		var el = this.element

		el.addClass('in')
		el.removeClass('out')
		el.removeClass('hide')

		this.backdrop.inject(el, 'before')


//		console.log('firevent show')
//		window.fireEvent('brickrouge.modal.show', { modal: this })
	},

	hide: function()
	{
		var el = this.element

//		window.fireEvent('brickrouge.modal.hide', { modal: this })

		el.removeClass('in')
		el.addClass('out')
		el.addClass('hide')

		this.backdrop.dispose()
	},

	isHidden: function()
	{
		return this.element.hasClass('hide')
	},

	toggle: function()
	{
		this.isHidden() ? this.show() : this.hide()
	}
})

window.addEvent('click:relay([data-toggle="modal"])', function(ev, el) {

	var modalId = el.get('href').substring(1)
	, modalEl = $(modalId)
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

})