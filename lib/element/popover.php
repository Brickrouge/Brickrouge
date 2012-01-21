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

/**
 * Creates a popover element.
 *
 * BrickRouge provides the BrickRouge.Popover Javascript class that can be used to give behaviour
 * to the element, but because the element is not a widget this is not automatic and left up to
 * you.
 *
 * Use the BrickRouge\PopoverWidget to create elements with automatically attached behaviour.
 */
class Popover extends Element
{
	const ACTIONS = '#actions';
	const ANCHOR = '#anchor';
	const POSITION = '#position';

	/**
	 * Constructor.
	 *
	 * The `class` attribute is defined in the initial tags with the value "popover". The "popover"
	 * class is used to style the element but can also be used to give the element a behaviour. If
	 * you override the `class` attribute you should consider adding the "popover" class name.
	 *
	 * The element is created as a DIV element.
	 *
	 * @param array $tags
	 */
	public function __construct(array $tags=array())
	{
		parent::__construct
		(
			'div', $tags + array
			(
				'class' => 'popover'
			)
		);
	}

	/**
	 * Adds the position defined using the {@link POSITION} attribute to the composed class.
	 *
	 * @see BrickRouge.Element::__volatile_get_class()
	 */
	protected function __volatile_get_class()
	{
		$class = parent::__volatile_get_class();

		$position = $this[self::POSITION];

		if ($position)
		{
			$class .= ' ' . $position;
		}

		return $class;
	}

	/**
	 * The inner HTML is wrapped in a number of DIV elements, and the legend is used a the popover
	 * title.
	 *
	 * @see BrickRouge.Element::render_inner_html()
	 */
	protected function render_inner_html()
	{
		$content = parent::render_inner_html();

		$legend = $this[self::LEGEND];

		if ($legend)
		{
			$legend = '<h3 class="title">' . escape($legend) . '</h3>';
		}

		$actions = $this[self::ACTIONS];

		if ($actions)
		{
			$actions = $this->render_actions($actions);
		}

		return <<<EOT
<div class="arrow"></div>
<div class="inner">$legend<div class="content">$content</div>$actions</div>
EOT;
	}

	/**
	 * Renders actions.
	 *
	 * Actions can be provided as a special value, an array of button elements or a string.
	 *
	 * If actions are provided as the special value "boolean", an array of buttons is created. The
	 * array contains two buttons: a _cancel_ and a _ok_ button.
	 *
	 * Buttons should provide a `data-action` attribute with the value of the action to use
	 * when the `action` event is fired by Javascript.
	 *
	 * @param mixed $actions
	 *
	 * @return string
	 */
	protected function render_actions($actions)
	{
		if ($actions == 'boolean')
		{
			$actions = array
			(
				new Button('Cancel', array('data-action' => 'cancel')),
				new Button('Ok', array('class' => 'primary', 'data-action' => 'ok'))
			);
		}

		if (is_array($actions))
		{
			$actions = implode($actions);
		}

		return '<div class="actions">' . $actions . '</div>';
	}

	/**
	 * Adds the anchor specified using the {@linkk ANCHOR} special attribute to the dataset before
	 * it is rendered.
	 *
	 * @see BrickRouge.Element::render_dataset()
	 */
	protected function render_dataset(array $dataset)
	{
		return parent::render_dataset
		(
			$dataset + array
			(
				'anchor' => $this[self::ANCHOR],
				'position' => $this[self::POSITION]
			)
		);
	}

	/**
	 * The legend decoration is disabled because the {@link LEGEND} tag is already used by the
	 * {@link render_inner_html()} method for the popover title.
	 *
	 * @see BrickRouge.Element::decorate_with_legend()
	 */
	protected function decorate_with_legend($html, $legend)
	{
		return $html;
	}
}

/**
 * A popover element with automatically attached behaviour.
 */
class PopoverWidget extends Popover
{
	/**
	 * Overrides the {@link Popover} initial attribute `class` with the value
	 * "widget-popover popover". The "widget-popover" class is used to automatically attach
	 * popover behaviour to the element, while the "popover" class is used to style the element.
	 *
	 * If you override the `class` attribute, remember to define this two class names, unless
	 * you want to use a diffenrent behaviour or style.
	 *
	 * @param array $tags
	 */
	public function __construct(array $tags)
	{
		parent::__construct
		(
			$tags + array
			(
				'class' => 'widget-popover popover'
			)
		);
	}
}