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
	private $elements=array();

	public function __construct($tags)
	{
		parent::__construct
		(
			'div', $tags + array
			(
				self::CHILDREN => array
				(
					'q' => $this->elements['q'] = new Text(),

					$this->elements['trigger'] = new Button
					(
						'Search', array
						(
							'type' => 'submit'
						)
					)
				),

				'class' => 'widget-searchbox'
			)
		);
	}

	public function set($property, $value=null)
	{
		if (is_string($property))
		{
			if (in_array($property, array('name', 'value', 'placeholder')))
			{
				$this->elements['q']->set($property, $value);
			}
		}

		return parent::set($property, $value);
	}
}