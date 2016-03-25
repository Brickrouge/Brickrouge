define([

	'./Core',
	'./Modal'

], function (Brickrouge) {
	"use strict";

	document.body.addDelegatedEventListener('[data-toggle="modal"]', 'click', function(ev, el) {

		var modalId = el.get('href').substring(1)
		, modalEl = document.getElementById(modalId)

		if (!modalEl) return

		ev.preventDefault()
		ev.stopPropagation()

		Brickrouge.Modal.from(modalEl).toggle()
	})

	document.body.addDelegatedEventListener('[data-dismiss="modal"]', 'click', function(ev, el) {

		var modalEl = el.closest('.modal')
		, modal

		if (!modalEl) return

		ev.preventDefault()
		ev.stopPropagation()

		modal = Brickrouge.Modal.from('modal')

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
