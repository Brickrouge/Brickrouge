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

interface Validator
{
	/**
	 * Validates the value provided.
	 *
	 * @param mixed $value The value to validate.
	 * @param Errors $errors Used to collect error messages.
	 *
	 * @return boolean `true` if the value is valid, `false` otherwise.
	 */
	public function validate($value, Errors $errors);
}
