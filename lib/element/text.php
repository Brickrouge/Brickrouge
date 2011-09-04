<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element;

use BrickRouge\Element;

class Text extends Element
{
	public function __construct($tags)
	{
		parent::__construct
		(
			self::E_TEXT, $tags + array
			(
				'size' => 30
			)
		);
	}
}
