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

class DateTime extends Date
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				'size' => 24,
				'class' => 'datetime'
			)
		);
	}
}