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
 * An alert message.
 */
class Alert extends Element
{
	/**
	 * The context of the alert, one of 'error', 'success' or 'info'.
	 *
	 * @var string
	 */
	const CONTEXT = '#alert-context';

	/**
	 * The heading of the alert.
	 *
	 * @var string
	 */
	const HEADING = '#alert-heading';

	protected $message;
	protected $alert_type;

	/**
	 * Constructor.
	 *
	 * The element is created with the class "alert-message".
	 *
	 * @param string|array|ICanBoogie\Errors $message The alert message is provided as a string,
	 * an array of strings or an ICanBoogie\Errors object.
	 *
	 * If the message is provided as a string it is used as is. If the message is provided as an
	 * array each value of the array is considered as a message. If the message is provided as
	 * an ICanBoogie\Errors object each entry of the object is considered as a message.
	 *
	 * Each message is wrapped in a P element and they are concatenated to create the final
	 * message.
	 *
	 * If the message is an instance of \ICanBoogie\Errors the {@link CONTEXT} attribute is set to
	 * 'error' in the initial tags.
	 *
	 * @param array $tags Additional tags.
	 *
	 * @param string $type Defines an additionnal class for the element. If the message is an
	 * ICanBoogie\Errors object $type is set to "errors".
	 */
	public function __construct($message, $tags=array())
	{
		$this->message = $message;

		parent::__construct
		(
			'div', $tags + array
			(
				self::CONTEXT => $message instanceof Errors ? 'error' : null,

				'class' => 'alert'
			)
		);
	}

	/**
	 * Add the alert context to the class names.
	 *
	 * @see Brickrouge.Element::render_class()
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
	 * @see Brickrouge.Element::render_inner_html()
	 */
	public function render_inner_html()
	{
		$heading = $this[self::HEADING];

		if ($heading)
		{
			$heading = '<h4 class="alert-heading">' . escape($heading) . '</h4>';
		}

		$message = $this->message;

		if ($message instanceof Errors)
		{
			$errors = $message;
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

	/**
	 * An empty string is returned if there is no message.
	 *
	 * @see Brickrouge.Element::__toString()
	 */
	public function __toString()
	{
		$message = $this->message;

		if (($message instanceof Errors && !count($message)) || !$message)
		{
			return '';
		}

		return parent::__toString();
	}
}