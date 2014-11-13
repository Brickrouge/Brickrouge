<?php

namespace Brickrouge;

use ICanBoogie\Errors;

interface Validator
{
	/**
	 * Validates the value provided.
	 *
	 * @param mixed $value The value to validate.
	 * @param \ICanBoogie\Errors $errors Used to collect error messages.
	 *
	 * @return boolean true if the value is valid, false otherwise.
	 */
	public function validate($value, Errors $errors);
}
