<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element;

use BrickRouge;
use BrickRouge\Element;

class Searchbox extends Element
{
	private $elements=array();

	public function __construct($tags)
	{
		parent::__construct
		(
			'div', wd_array_merge_recursive
			(
				array
				(
					self::T_CHILDREN => array
					(
						'q' => $this->elements['q'] = new Element
						(
							self::E_TEXT, array
							(

							)
						),

						$this->elements['trigger'] = new Element
						(
							self::E_SUBMIT, array
							(
								self::T_INNER_HTML => t('Search')
							)
						)
					),

					'class' => 'widget-searchbox'
				),

				$tags
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