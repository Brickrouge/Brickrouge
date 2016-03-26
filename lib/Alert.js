/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Destroy the alert message when its close icon is clicked.
 *
 * If the alert message is in a FORM element the "error" class is removed from its elements.
 */
document.body.addDelegatedEventListener('[data-dismiss="alert"]', 'click', function(ev, target) {

	var alert = target.closest('.alert')
	, form = target.closest('form')

	if (alert) alert.remove()
	if (!form) return

	var brForm = form.get('brickrouge.form')

	/*
	 * If we clear the alert ourselves, the submit event is not triggered for some reason.
	 */
	if (brForm instanceof Brickrouge.Form)
	{
		brForm.clearAlert()

		return
	}

	form.querySelectorAll('.has-danger').forEach(function(el) {

		el.classList.remove('has-danger')

	})

})
