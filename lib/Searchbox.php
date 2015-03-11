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
	private $elements = [];

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [

			self::CHILDREN => [

				'q' => $this->elements['q'] = new Text(),

				$this->elements['trigger'] = new Button('Search', [

					'type' => 'submit'

				])
			],

			'class' => 'widget-searchbox'

		]);
	}

	/**
	 * @inheritdoc
	 */
	public function offsetSet($attribute, $value)
	{
		if (in_array($attribute, [ 'name', 'value', 'placeholder' ]))
		{
			$this->elements['q'][$attribute] = $value;
		}

		parent::offsetSet($attribute, $value);
	}
}
