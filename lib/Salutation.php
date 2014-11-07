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
	public function __construct(array $attributes=[], $type=self::TYPE_RADIO_GROUP)
	{
		$options = [ 'Misses', 'Miss', 'Mister' ];

		array_walk($options, function(&$v) {

			$v = $this->t($v, [], [ 'scope' => 'salutation' ]);

		});

		if ($type == 'select' && !empty($attributes[self::REQUIRED]))
		{
			$options = [ null => '' ] + $options;
		}

		parent::__construct($type, $attributes + [

			Form::LABEL => 'Salutation',
			Element::OPTIONS => $options,

			'class' => 'inline-inputs'

		]);
	}
}
