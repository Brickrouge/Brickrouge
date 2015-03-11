<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

use ICanBoogie\Errors;

/**
 * A `<DIV.alert>` element.
 */
class Alert extends Element
{
	/**
	 * The context of the alert, one of "error", "success" or "info".
	 */
	const CONTEXT = '#alert-context';
	const CONTEXT_SUCCESS = 'success';
	const CONTEXT_INFO = 'info';
	const CONTEXT_ERROR = 'error';

	/**
	 * The heading of the alert.
	 */
	const HEADING = '#alert-heading';

	/**
	 * Set to `true` for undismissable alerts.
	 */
	const UNDISMISSABLE = '#alert-undismissable';

	const DISMISS_BUTTON = '<button type="button" class="close" data-dismiss="alert">×</button>';

	/**
	 * Alert message.
	 */
	protected $message;

	/**
	 * Alert type, one of "error", "success" or "info".
	 */
	protected $alert_type;

	/**
	 * Creates a `<DIV.alert>` element.
	 *
	 * @param string|array|Errors $message The alert message is provided as a string,
	 * an array of strings or a {@link Errors} object.
	 *
	 * If the message is provided as a string it is used as is. If the message is provided as an
	 * array each value of the array is considered as a message. If the message is provided as
	 * an {@link Errors} object each entry of the object is considered as a message.
	 *
	 * Each message is wrapped in a `<P>` element and they are concatenated to create the final
	 * message.
	 *
	 * If the message is an instance of {@link Errors} the {@link CONTEXT} attribute is
	 * set to "error" in the initial attributes.
	 *
	 * @param array $attributes Additional attributes.
	 */
	public function __construct($message, array $attributes = [])
	{
		$this->message = $message;

		parent::__construct('div', $attributes + [

			self::CONTEXT => $message instanceof Errors ? 'error' : null,

			'class' => 'alert'

		]);
	}

	/**
	 * Adds the `alert-error`, `alert-info` and `alert-success` class names according to the
	 * {@link CONTEXT} attribute.
	 *
	 * Adds the `alert-block` class name if the {@link HEADING} attribute is defined.
	 *
	 * Adds the `undismissable` class name if the {@link UNDISMISSABLE} attribute is true.
	 *
	 * @inheritdoc
	 */
	protected function alter_class_names(array $class_names)
	{
		$class_names = parent::alter_class_names($class_names);

		$context = $this[self::CONTEXT];

		if ($context)
		{
			$class_names['alert-' . $context] = true;
		}

		if ($this[self::HEADING])
		{
			$class_names['alert-block'] = true;
		}

		if ($this[self::UNDISMISSABLE])
		{
			$class_names['undismissable'] = true;
		}

		return $class_names;
	}

	/**
	 * @throws ElementIsEmpty if the message is empty.
	 *
	 * @inheritdoc
	 */
	public function render_inner_html()
	{
		$heading = $this[self::HEADING];

		if ($heading)
		{
			$heading = '<h4 class="alert-heading">' . escape($heading) . '</h4>';
		}

		$message = $this->message;

		if (!$message)
		{
			throw new ElementIsEmpty;
		}
		if ($message instanceof Errors)
		{
			$errors = $message;

			if (!count($errors))
			{
				throw new ElementIsEmpty;
			}

			$message = '';

			foreach ($errors as $error)
			{
				if ($error === true)
				{
					continue;
				}

				$message .= '<p>' . $error . '</p>';
			}
		}
		else if (is_array($message))
		{
			$message = '<p>' . implode('</p><p>', $message) . '</p>';
		}

		$dismiss = '';

		if (!$this[self::UNDISMISSABLE])
		{
			$dismiss = self::DISMISS_BUTTON;
		}

		return $dismiss . $heading . '<div class="content">' . $message . '</div>';
	}
}
