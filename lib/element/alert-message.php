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

class AlertMessage extends Element
{
	protected $message;
	protected $alert_type;

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

	public function compose_class()
	{
		return parent::compose_class() . ' ' . $this->alert_type;
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

		return <<<EOT
<a href="#close" class="close">×</a>
$message
EOT;
	}

	/**
	 * An empty string is returned if there is no message.
	 *
	 * @see BrickRouge.Element::__toString()
	 */
	public function __toString()
	{
		$message = $this->message;

		if ($message instanceof Errors && !count($message))
		{
			return '';
		}

		return parent::__toString();
	}
}