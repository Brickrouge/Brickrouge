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
 * An actions element that can be used with forms or popovers.
 */
class Actions extends Element
{
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
	public function __construct($actions, array $attributes=[])
	{
		$this->actions = $actions;

		parent::__construct('div', $attributes + [ 'class' => 'actions' ]);
	}

	/**
	 * Renders the actions.
	 *
	 * If actions are defined as the string "boolean" they are replaced by an array with the
	 * buttons `button[data-action="cancel"]` and
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
		$html = parent::render_inner_html();
		$actions = $this->actions;

		if ($actions == 'boolean')
		{
			$actions = [

				new Button('Cancel', [ 'data-action' => 'cancel' ]),
				new Button('Ok', [ 'data-action' => 'ok', 'type' => 'submit', 'class' => 'btn-primary' ]),

			];
		}

		if (is_array($actions))
		{
			foreach ($actions as $name => $action)
			{
				if (!is_string($name) || !($action instanceof Element) || $action['name'] !== null)
				{
					continue;
				}

				$action['name'] = $name;
			}

			$actions = implode($actions);
		}
		else if ($actions === true)
		{
			$actions = new Button('Ok', [ 'dataset' => [ 'action' => 'ok' ], 'type' => 'submit', 'class' => 'btn-primary' ]);
		}

		return $html . $actions;
	}
}
