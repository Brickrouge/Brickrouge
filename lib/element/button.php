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

class Button extends Element
{
	public function __construct($label, $tags=array())
	{
		parent::__construct
		(
			'button', $tags + array
			(
				'type' => 'button',

				self::T_INNER_HTML => escape(t($label, array(), array('scope' => 'button')))
			)
		);
	}
}
