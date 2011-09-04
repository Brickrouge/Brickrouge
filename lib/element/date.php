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

class Date extends Element
{
	public function __construct($tags, $dummy=null)
	{
		global $document;

		parent::__construct
		(
			Element::E_TEXT, $tags + array
			(
				'size' => 16,
				'class' => 'date'
			)
		);

		if (isset($document))
		{
			$document->js->add(BrickRouge\ASSETS . 'element/datepicker/datepicker.js');
			$document->js->add(BrickRouge\ASSETS . 'element/datepicker/auto.js');

			$document->css->add(BrickRouge\ASSETS . 'element/datepicker/calendar-eightysix-v1.1-default.css');
			$document->css->add(BrickRouge\ASSETS . 'element/datepicker/calendar-eightysix-v1.1-osx-dashboard.css');
		}
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
}