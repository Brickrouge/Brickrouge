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

class Salutation extends Element
{
	public function __construct(array $tags=array(), $type=self::TYPE_RADIO_GROUP)
	{
		$options = array
		(
			'Misses',
			'Miss',
			'Mister'
		);

		array_walk($options, function(&$v) {

			$v = t($v, array(), array('scope' => 'salutation'));

		});

		if ($type == 'select' && !empty($tags[self::REQUIRED]))
		{
			$options = array(null => '') + $options;
		}

		parent::__construct
		(
			$type, $tags + array
			(
				Form::LABEL => 'Salutation',
				Element::OPTIONS => $options,

				'class' => 'inline-inputs'
			)
		);
	}
}