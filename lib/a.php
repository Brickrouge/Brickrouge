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
 * A `<A>` element.
 */
class A extends Element
{
	/**
 	 * @param string|Element $label Defines the content of the element. If `$label` is not
	 * a {@link Element} instance it is escaped.
	 * @param string $href URI for linked resource.
	 * @param array $attributes Optional attributes.
	 *
	 * @example
	 *
	 * <?php echo new A('Brickrouge', 'http://brickrouge.org');
	 */
	public function __construct($label, $href='#', array $attributes=array())
	{
		if (!($label instanceof HTMLStringInterface))
		{
			$label = escape(t($label));
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
