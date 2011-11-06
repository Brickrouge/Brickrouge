<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

/**
 * Creates a "A" element.
 */
class A extends Element
{
	/**
	 * Constructor.
	 *
	 * @param string $label Defines the content of the element. The value is translated with the
	 * scope "a" and escaped.
	 * @param string $href URI for linked resource.
	 * @param array $tags Optional tags.
	 *
	 * Example:
	 *
	 * echo new A('BrickRouge', 'http://brickrouge.org');
	 */
	public function __construct($label, $href='#', array $tags=array())
	{
		parent::__construct
		(
			'a', $tags + array
			(
				'href' => $href,

				self::INNER_HTML => escape(t($label, array(), array('scope' => 'a')))
			)
		);
	}
}
