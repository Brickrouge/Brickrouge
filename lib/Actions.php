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

/**
 * An actions element that may be used with forms and popovers.
 */
class Actions extends Element
{
	const ACTIONS_BOOLEAN = 'boolean';

	/**
	 * Actions.
	 *
	 * @var string|array
	 */
	protected $actions;

	/**
	 * @param boolean|array|string|Element $actions Actions can be defined as a boolean, an array
	 * a string or an instance of the {@link Element} class. @see {@link render_inner_html()}
	 *
	 * @param array $attributes
	 */
	public function __construct($actions, array $attributes = [])
	{
		$this->actions = $actions;

		parent::__construct('div', $attributes + [ 'class' => 'actions' ]);
	}

	/**
	 * Renders the actions.
	 *
	 * If actions are defined as the string "boolean" they are replaced by an array with the
	 * buttons `button[data-action="cancel"].btn-secondary` and
	 * `button[data-action="ok"][type=submit].btn-primary`.
	 *
	 * If actions are defined as a boolean, they are replaced by a
	 * `button[type=submit][data-action="ok"].btn-primary` element with the label "Send".
	 *
	 * If actions are defined as an array, the array is concatenated with the glue
	 * `<span class="separator">&nbsp;</span>`.
	 *
	 * Otherwise actions are used as is.
	 */
	protected function render_inner_html()
	{
		return parent::render_inner_html() . $this->render_actions($this->actions);
	}

	/**
	 * Renders actions.
	 *
	 * @param mixed $actions
	 *
	 * @return string
	 */
	protected function render_actions($actions)
	{
		if ($actions == self::ACTIONS_BOOLEAN)
		{
			$actions = [

				new Button("Cancel", [ 'data-action' => 'cancel', 'class' => 'btn-secondary' ]),
				new Button("Ok", [ 'data-action' => 'ok', 'class' => 'btn-primary', 'type' => 'submit' ]),

			];
		}

		if (is_array($actions))
		{
			foreach ($actions as $name => $action)
			{
				if (!is_string($name) || !$action instanceof Element || $action['name'] !== null)
				{
					continue;
				}

				$action['name'] = $name;
			}

			return implode($actions);
		}

		if ($actions === true)
		{
			return new Button("Ok", [ 'data-action' => 'ok', 'class' => 'btn-primary', 'type' => 'submit' ]);
		}

		return $actions;
	}
}
