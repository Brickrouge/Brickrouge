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

class Actions extends Element
{
	/**
	 *
	 * Enter description here ...
	 * @var unknown_type
	 */
	protected $actions;

	/**
	 *
	 * Enter description here ...
	 * @param boolean|array|string $actions Actions can be defined as a boolean, an array or a
	 *
	 * If actions are defined as the string "boolean" they are replaced by an array with the
	 * buttons "button[data-action="cancel"]" and
	 * "button[data-action="ok"][type=submit][class=primary]".
	 *
	 * If actions are defined as a boolean, they are replaced by a
	 * "button[type=submit][data-action="ok"].primary" element with the label "Send".
	 *
	 * If actions are defined as an array, the array is concatened with the glue
	 * "<span class="separator">&nbsp;</span>".
	 *
	 * Otherwise actions are used as is.
	 *
	 * @param array $attributes
	 */
	public function __construct($actions, array $attributes=array())
	{
		$this->actions = $actions;

		parent::__construct('div', $attributes + array('class' => 'actions'));
	}

	protected function render_inner_html()
	{
		$html = parent::render_inner_html();
		$actions = $this->actions;

		if ($actions == 'boolean')
		{
			$actions = array
			(
				new Button('Cancel', array('data-action' => 'cancel')),
				new Button('Ok', array('data-action' => 'ok', 'type' => 'submit', 'class' => 'primary')),
			);
		}

		if (is_array($actions))
		{
			$actions = implode('<span class="separator">&nbsp;</span>', $actions);
		}
		else if ($actions === true)
		{
			$actions = new Button('Ok', array('dataset' => array('action' => 'ok'), 'type' => 'submit', 'class' => 'primary'));
		}

		return $html . $actions;
	}
}