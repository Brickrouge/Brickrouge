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
 * A `<button>` element.
 */
class Button extends Element
{
	/**
	 * Constructor.
	 *
	 * The object is created with the "button" type and an union of the provided tags and the
	 * following values:
	 *
	 * - 'type': "button"
	 * - INNER_HTML: The translated and escaped label. The label is translated with the "button"
	 * scope.
	 *
	 * @param string $label Label of the button (inner text).
	 * @param array $attributes Optional attributes used to create the element.
	 */
	public function __construct($label, array $attributes=array())
	{
		parent::__construct
		(
			'button', $attributes + array
			(
				'type' => 'button',

				self::INNER_HTML => escape(t($label, array(), array('scope' => 'button')))
			)
		);
	}
}