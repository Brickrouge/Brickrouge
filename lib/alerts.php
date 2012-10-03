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
	 *
	 * @var string
	 */
	const CONTEXT = '#alert-context';
	const CONTEXT_SUCCESS = 'success';
	const CONTEXT_INFO = 'info';
	const CONTEXT_ERROR = 'error';

	/**
	 * The heading of the alert.
	 *
	 * @var string
	 */
	const HEADING = '#alert-heading';

	/**
	 * Alert message.
	 *
	 * @var string|array|\ICanBoogie\Errors
	 */
	protected $message;

	/**
	 * Alert type, one of "error", "success" or "info".
	 *
	 * @var string
	 */
	protected $alert_type;

	/**
	 * Creates a `<DIV.alert>` element.
	 *
	 * @param string|array|\ICanBoogie\Errors $message The alert message is provided as a string,
	 * an array of strings or a {@link \ICanBoogie\Errors} object.
	 *
	 * If the message is provided as a string it is used as is. If the message is provided as an
	 * array each value of the array is considered as a message. If the message is provided as
	 * an {@link Errors} object each entry of the object is considered as a message.
	 *
	 * Each message is wrapped in a `<P>` element and they are concatenated to create the final
	 * message.
	 *
	 * If the message is an instance of {@link \ICanBoogie\Errors} the {@link CONTEXT} attribute is
	 * set to "error" in the initial attributes.
	 *
	 * @param array $attributes Additional attributes.
	 */
	public function __construct($message, array $attributes=array())
	{
		$this->message = $message;

		parent::__construct
		(
			'div', $attributes + array
			(
				self::CONTEXT => $message instanceof Errors ? 'error' : null,

				'class' => 'alert'
			)
		);
	}

	/**
	 * Adds the alert context to the class names.
	 *
	 * If the {@link HEADING} attribute is defined the `alert-block` class name is added.
	 *
	 * @see Element::render_class()
	 */
	protected function render_class(array $class_names)
	{
		$context = $this[self::CONTEXT];

		if ($context)
		{
			$class_names['alert-' . $context] = true;
		}

		if ($this[self::HEADING])
		{
			$class_names['alert-block'] = true;
		}

		return parent::render_class($class_names);
	}

	/**
	 * Renders the inner HTML of the element with the following template:
	 *
	 * <a href="javascript://" class="close">×</a>
	 * [<h4>$heading</h4>]
	 * <div class="content">$message</div>
	 *
	 * @throws ElementIsEmpty if the message is empty.
	 *
	 * @see Element::render_inner_html()
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

		return '<a href="javascript://" class="close">×</a>' . $heading . '<div class="content">' . $message . '</div>';
	}
}