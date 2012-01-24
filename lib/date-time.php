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

class DateTime extends Date
{
	public function __construct(array $tags)
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