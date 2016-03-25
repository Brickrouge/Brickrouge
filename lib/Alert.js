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
document.body.addEvent('click:relay([data-dismiss="alert"])', function(ev, target) {

	var alert = target.getParent('.alert')
	, form = target.getParent('form')

	if (alert) alert.destroy()

	if (form)
	{
		var brForm = form.get('brickrouge.form')

		/*
		 * If we clear the alert ourselves, the submit event is not triggered for some reason.
		 */
		if (brForm instanceof Brickrouge.Form)
		{
			brForm.clearAlert()

			return
		}

		form.getElements('.has-danger').removeClass('has-danger')
	}

})
