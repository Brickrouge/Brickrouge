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

class Date extends Text
{
	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			$tags + array
			(
				'size' => 16,
				'class' => 'date'
			)
		);
	}

	public function __toString()
	{
		$value = $this->get('value');

		if (!(int) $value)
		{
			$this->set('value', null);
		}

		return parent::__toString();
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add(ASSETS . 'element/datepicker/datepicker.js');
		$document->js->add(ASSETS . 'element/datepicker/auto.js');

		$document->css->add(ASSETS . 'element/datepicker/calendar-eightysix-v1.1-default.css');
		$document->css->add(ASSETS . 'element/datepicker/calendar-eightysix-v1.1-osx-dashboard.css');
	}
}