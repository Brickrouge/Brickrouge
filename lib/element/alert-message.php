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
	protected $alter_type;

	public function __construct($message, $tags=array(), $type='')
	{
		$this->message = $message;

		if ($message instanceof Errors)
		{
			$this->alter_type = 'error';
		}
		else
		{
			$this->alter_type = $type;
		}

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
		return parent::compose_class() . ' ' . $this->alter_type;
	}

	public function render_inner_html()
	{
		$message = $this->message;

		if ($message instanceof Errors)
		{
			$rc = '';

			foreach ($message as $m)
			{
				if ($m === true)
				{
					continue;
				}

				$rc .= '<p>' . $m . '</p>';
			}

			$message = $rc;
		}

		return <<<EOT
<a href="#close" class="close">×</a>
$message
EOT;
	}
}