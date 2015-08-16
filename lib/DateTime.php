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

/**
 * @deprecated
 */
class DateTime extends Date
{
	public function __construct(array $attributes = [])
	{
		parent::__construct($attributes + [

			'size' => 24,
			'class' => 'datetime'

		]);
	}
}
