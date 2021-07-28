<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Validate\FormValidator;

use ICanBoogie\ErrorCollection;

/**
 * An interface for a callable that validates values against a set of rules.
 */
interface ValidateValues
{
    /**
     * Validate values against a set of rules.
     *
     * @param array<string, mixed> $values Values to validate.
     * @param array<string, mixed> $rules Validation rules.
     * @param ErrorCollection $errors Used to collect errors.
     *
     * @return void
     */
    public function __invoke(array $values, array $rules, ErrorCollection $errors): void;
}
