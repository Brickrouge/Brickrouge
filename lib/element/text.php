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

class Text extends Element
{
	public function __construct($tags=array())
	{
		parent::__construct
		(
			'input', $tags + array
			(
				'type' => 'text'
			)
		);
	}
}
