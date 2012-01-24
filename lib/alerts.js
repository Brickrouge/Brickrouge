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
document.id(document.body).addEvent
(
	'click:relay(.alert a.close)', function(ev, target)
	{
		ev.stop();

		var form = target.getParent('form');

		if (form) {

			form.getElements('.error').removeClass('error');
		}

		target.getParent('.alert').destroy();
	}
);
