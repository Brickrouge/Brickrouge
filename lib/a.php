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
 * Creates a "A" element.
 */
class A extends Element
{
	/**
	 * Constructor.
	 *
	 * @param string|Element $label Defines the content of the element. If the $label is not
	 * a Element object it is escaped.
	 * @param string $href URI for linked resource.
	 * @param array $tags Optional tags.
	 *
	 * Example:
	 *
	 * echo new A('Brickrouge', 'http://brickrouge.org');
	 */
	public function __construct($label, $href='#', array $attributes=array())
	{
		if (is_string($label)
		{
			$label = escape($label);	
		}

		parent::__construct
		(
			'a', $attributes + array
			(
				'href' => $href,

				self::INNER_HTML => $label
			)
		);
	}
}
