define([

	'./Modal'

], function (Modal) {

	"use strict";

	document.body.addDelegatedEventListener('[data-toggle="modal"]', 'click', (ev, el) => {

		let modalId = el.get('href').substring(1)
		let modalEl = document.getElementById(modalId)

		if (!modalEl) return

		ev.preventDefault()
		ev.stopPropagation()

		Modal.from(modalEl).toggle()

	})

	document.body.addDelegatedEventListener('[data-dismiss="modal"]', 'click', (ev, el) => {

		let modalEl = el.closest('.modal')

		if (!modalEl) return

		ev.preventDefault()
		ev.stopPropagation()

		let modal = Modal.from('modal')

		if (modal)
		{
			modal.hide()
		}
		else
		{
			modalEl.classList.add('hide')
		}

	})

})
