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

class Searchbox extends Element
{
	private $elements=[];

	public function __construct($tags)
	{
		parent::__construct('div', $tags + [

			self::CHILDREN => [

				'q' => $this->elements['q'] = new Text(),

				$this->elements['trigger'] = new Button('Search', [

					'type' => 'submit'

				])
			],

			'class' => 'widget-searchbox'

		]);
	}

	public function offsetSet($offset, $value)
	{
		if (in_array($offset, [ 'name', 'value', 'placeholder' ]))
		{
			$this->elements['q'][$offset] = $value;
		}

		return parent::offsetSet($offset, $value);
	}
}
