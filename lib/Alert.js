/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([

	'./Core'

], function (Brickrouge) {

	/**
	 * Destroy the alert message when its close icon is clicked.
	 *
	 * If the alert message is in a FORM element the "error" class is removed from its elements.
	 */
	document.body.addDelegatedEventListener('[data-dismiss="alert"]', 'click', (ev, target) => {

		let alert = target.closest('.alert')
		let form = target.closest('form')

		if (alert) alert.remove()
		if (!form) return

		try
		{
			Brickrouge.Form.from(form).clearAlert()
		}
		catch (e) {}

		form.querySelectorAll('.has-danger').forEach(el => {

			el.classList.remove('has-danger')

		})

	})

})
