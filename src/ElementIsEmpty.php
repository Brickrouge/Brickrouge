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

use Exception;
use Throwable;

/**
 * Exception thrown when one wants to cancel the whole rendering of an empty element. The
 * {@link Element} class takes care of this special case and instead of rendering the exception
 * only returns an empty string as the result of its {@link Element::render()} method.
 */
class ElementIsEmpty extends Exception
{
    /**
     * @inheritdoc
     */
    public function __construct(string $message = "The element is empty.", Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
