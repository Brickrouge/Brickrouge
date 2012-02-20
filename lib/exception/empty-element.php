<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Exception;

/**
 * The EmptyElement exception is usually thrown when one wants to cancel the whole element
 * rendering because its inner HTML is empty. The {@link \Brickrouge\Element} class takes care of
 * this special case and instead of rendering the exception only returns an empty string as the
 * result of its __toString() method.
 */
class EmptyElement extends \Exception
{
	public function __construct($message="The inner HTML of the element is empty.", $code=500, \Exception $previous=null)
	{
		parent::__construct($message, $code, $previous);
	}
}