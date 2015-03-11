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
 * Exception thrown when one wants to cancel the whole rendering of an empty element. The
 * {@link Element} class takes care of this special case and instead of rendering the exception
 * only returns an empty string as the result of its {@link Element::render()} method.
 */
class ElementIsEmpty extends \Exception
{
	public function __construct($message = "The element is empty.", $code = 200, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
