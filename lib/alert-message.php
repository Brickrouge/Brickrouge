<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

use ICanBoogie\Errors;

/**
 * Creates alert message blocks.
 */
class AlertMessage extends Element
{
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
	 * @param array $tags Additional tags.
	 *
	 * @param string $type Defines an additionnal class for the element. If the message is an
	 * ICanBoogie\Errors object $type is set to "errors".
	 */
	public function __construct($message, $tags=array(), $type='')
	{
		$this->message = $message;
		$this->alert_type = $message instanceof Errors ? 'error' : $type;

		parent::__construct
		(
			'div', $tags + array
			(
				'class' => 'alert-message'
			)
		);
	}

	/**
	 * Add the alert type to the class string.
	 *
	 * @see BrickRouge.Element::__volatile_get_class()
	 */
	protected function __volatile_get_class()
	{
		return parent::__volatile_get_class() . ' ' . $this->alert_type;
	}

	public function render_inner_html()
	{
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

		return '<a href="#close" class="close">×</a>' . $message;
	}

	/**
	 * An empty string is returned if there is no message.
	 *
	 * @see BrickRouge.Element::__toString()
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