Brickrouge.Modal = new Class({

	initialize: function(el, options)
	{
		this.element = el = document.id(el)
		this.backdrop = new Element('div.modal-backdrop')

		this.header = new Element('div.modal-header').adopt
		([
			new Element('button.close[data-dismiss="modal"][aria-hidden="true"]', { html: "&times;"}),
			new Element('h3', { html: 'Title' })
		])

		this.body = new Element('div.modal-body').adopt(el)
		this.footer = new Element('div.modal-footer')

		this.modal = new Element('div.modal.hide.fade').adopt([ this.header, this.body, this.footer ])
	},

	show: function()
	{
		this.modal.addClass('in')
		this.modal.removeClass('out')
		this.modal.removeClass('hide')

//		console.log('firevent show')
//		window.fireEvent('brickrouge.modal.show', { modal: this })
	},

	hide: function()
	{
//		window.fireEvent('brickrouge.modal.hide', { modal: this })

		this.modal.removeClass('in')
		this.modal.addClass('out')
		this.modal.addClass('hide')
	}
})
