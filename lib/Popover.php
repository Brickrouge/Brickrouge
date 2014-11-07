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
 * Creates a popover element.
 *
 * Brickrouge provides the Brickrouge.Popover Javascript class that can be used to give behaviour
 * to the element, but because the element is not a widget this is not automatic and left up to
 * you.
 *
 * Use the Brickrouge\PopoverWidget to create elements with automatically attached behaviour.
 */
class Popover extends Element
{
	/**
	 * Popover actions.
	 *
	 * @var string|array
	 */
	const ACTIONS = '#actions';

	/**
	 * Anchor ID or CSS selector.
	 *
	 * @var string
	 */
	const ANCHOR = '#anchor';

	/**
	 * Whether the popover element should fit the content.
	 *
	 * By default the popover element have a width of 280px. Setting this attribute to true adds
	 * the 'fit-content' class to the element which remove the width constraint.
	 *
	 * @var bool
	 */
	const FIT_CONTENT = '#fit-content';

	/**
	 * Placement of the popover relative to its anchor, one of `before`, `after`, `above`,
	 * `below`, `vertical`, `horizontal` or `auto`.
	 *
	 * @var string
	 */
	const PLACEMENT = '#placement';

	/**
	 * Optional title of the popover.
	 *
	 * @var string
	 */
	const TITLE = '#title';

	/**
	 * Constructor.
	 *
	 * The `class` attribute is defined in the initial tags with the value "popover". The "popover"
	 * class is used to style the element but can also be used to give the element a behaviour. If
	 * you override the `class` attribute you should consider adding the "popover" class name.
	 *
	 * The element is created as a DIV element.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes=[])
	{
		parent::__construct('div', $attributes + [

			'class' => 'popover'

		]);
	}

	/**
	 * Adds the 'fit-content' class name if the {@link FIT_CONTENT} attribute is truthy.
	 */
	protected function alter_class_names(array $class_names)
	{
		$class_names = parent::alter_class_names($class_names);

		if ($this[self::FIT_CONTENT])
		{
			$class_names['fit-content'] = true;
		}

		return $class_names;
	}

	/**
	 * Adds the anchor specified using the {@link ANCHOR} special attribute to the dataset before
	 * it is rendered.
	 */
	protected function alter_dataset(array $dataset)
	{
		return parent::alter_dataset($dataset + [

			'anchor' => $this[self::ANCHOR],
			'placement' => $this[self::PLACEMENT]

		]);
	}

	/**
	 * The inner HTML is wrapped in a number of DIV elements, and the title is used a the popover
	 * title.
	 */
	protected function render_inner_html()
	{
		$content = parent::render_inner_html();

		$title = $this[self::TITLE];

		if ($title)
		{
			$title = '<h3 class="popover-title">' . escape($title) . '</h3>';
		}

		$actions = $this[self::ACTIONS];

		if ($actions)
		{
			$actions = $this->render_actions($actions);
		}

		return <<<EOT
<div class="arrow"></div>
<div class="popover-inner">$title<div class="popover-content">$content</div>$actions</div>
EOT;
	}

	/**
	 * Renders actions.
	 *
	 * Actions are rendering using a {@link Actions} element.
	 *
	 * Actions buttons should provide a `data-action` attribute with the value of the action to use
	 * when the `action` event is fired by Javascript.
	 *
	 * @param mixed $actions
	 *
	 * @return string
	 */
	protected function render_actions($actions)
	{
		return new Actions($actions, [ 'class' => 'popover-actions' ]);
	}
}
