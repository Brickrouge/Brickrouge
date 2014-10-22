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

class DateRange extends Element
{
	const T_START_TAGS = '#daterange-start-tags';
	const T_FINISH_TAGS = '#daterange-finish-tags';

	public function __construct($tags=array(), $dummy=null)
	{
		$start_tags = isset($tags[self::T_START_TAGS]) ? $tags[self::T_START_TAGS] : array();
		$finish_tags = isset($tags[self::T_FINISH_TAGS]) ? $tags[self::T_FINISH_TAGS] : array();

		parent::__construct
		(
			'div', $tags + array
			(
				self::CHILDREN => array
				(
					new Date
					(
						$start_tags + array
						(
							self::LABEL => 'Début',
							self::LABEL_POSITION => 'before',

							'name' => 'start'
						)
					),

					' &nbsp; ',

					new Date
					(
						$finish_tags + array
						(
							self::LABEL => 'Fin',
							self::LABEL_POSITION => 'before',

							'name' => 'finish'
						)
					)
				),

				'class' => 'wd-daterange'
			)
		);
	}
}