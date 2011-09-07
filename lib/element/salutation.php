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

class Salutation extends Element
{
	public function __construct($tags, $type=self::E_RADIO_GROUP)
	{
		$options = array
		(
			'.Misses',
			'.Miss',
			'.Mister'
		);

		if ($type == 'select' && !empty($tags[self::T_REQUIRED]))
		{
			$options = array(null => '') + $options;
		}

		parent::__construct
		(
			$type, $tags + array
			(
				Form::T_LABEL => '.Salutation',
				Element::T_OPTIONS => $options
			)
		);
	}
}