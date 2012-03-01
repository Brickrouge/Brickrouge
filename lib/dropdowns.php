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

class DropdownMenu extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('ul', $attributes);
	}

	protected function render_inner_html()
	{
		$html = '';
		$options = $this[self::OPTIONS];
		$value = $this['value'];

		if ($value === null)
		{
			$value = $this[self::DEFAULT_VALUE];
		}

		foreach ($options as $key => $option)
		{
			if ($option === false)
			{
				$html .= '<li class="divider"></li>';

				continue;
			}
			else if ($option === null)
			{
				continue;
			}

			$html .= '<li' . ((string) $key === (string) $value ? ' class="active"' : '') . '>';

			if ($option instanceof Element)
			{
				$html .= $option;
			}
			else
			{
				$html .= '<a href="#" data-key="' . escape($key) . '">' . (is_string($option) ? escape($option) : $option) . '</a>';
			}

			$html .= '</li>';
		}

		return $html;
	}

	protected function render_class(array $class_names)
	{
		return parent::render_class($class_names + array('dropdown-menu' => true));
	}
}

class SplitButton extends Element
{
	public function __construct($label, array $attributes=array())
	{
		if (is_string($label))
		{
			$label = escape(t($label, array(), array('scope' => 'button')));
		}

		parent::__construct
		(
			'div', $attributes + array
			(
				self::INNER_HTML => $label
			)
		);
	}

	/**
	 * Renders the button and dropdown trigger button.
	 *
	 * The 'btn-primary', 'btn-danger', 'btn-success' and 'btn-info' class names are forwarded to
	 * the buttons.
	 *
	 * @see Brickrouge.Element::render_inner_html()
	 */
	protected function render_inner_html()
	{
		$label = parent::render_inner_html();

		$class_names = array_intersect_key
		(
			array
			(
				'btn-primary' => true,
				'btn-danger' => true,
				'btn-success' => true,
				'btn-info' => true
			),

			$this->class_names
		);

		$class = implode(' ', array_keys(array_filter($class_names)));

		return $this->render_splitbutton_label($label, $class)
		. $this->render_splitbutton_toggle($class)
		. $this->resolve_options($this[self::OPTIONS]);
	}

	/**
	 * Renders the button part of the element.
	 *
	 * @param string $label Label of the button. The label is already a HTML string. It doesn't
	 * need to be escaped.
	 * @param string $class Class of the label.
	 *
	 * @return string A HTML string.
	 */
	protected function render_splitbutton_label($label, $class)
	{
		return <<<EOT
<a href="javascript:void()" class="btn $class">$label</a>
EOT;
	}

	/**
	 * Renders the dropdown toggle part of the element.
	 *
	 * @param string $class Class of the element.
	 *
	 * @return string A HTML string.
	 */
	protected function render_splitbutton_toggle($class)
	{
		return <<<EOT
<a href="javascript:void()" class="btn dropdown-toggle $class" data-toggle="dropdown"><span class="caret"></span></a>
EOT;
	}

	/**
	 * Removes the 'btn-primary', 'btn-danger', 'btn-success' and 'btn-info' class names and adds
	 * the 'btn-group' class.
	 *
	 * @see Brickrouge.Element::render_class()
	 */
	protected function render_class(array $class_names)
	{
		return parent::render_class
		(
			array
			(
				'btn-primary' => false,
				'btn-danger' => false,
				'btn-success' => false,
				'btn-info' => false
			)

			+ $class_names + array('btn-group' => true)
		);
	}

	/**
	 * Resolves the provided options into a DropdownMenu element.
	 *
	 * @param mixed $options
	 *
	 * @throws \UnexpectedValueException If the provided options cannot be resolved into a
	 * DropdownMenu element.
	 *
	 * @return \Brickrouge\DropdownMenu
	 */
	protected function resolve_options($options)
	{
		if (is_array($options))
		{
			$options = new DropdownMenu(array(Element::OPTIONS => $options, 'value' => $this['value'] ?: $this[self::DEFAULT_VALUE]));
		}

		if (!($options instanceof DropdownMenu))
		{
			throw new \UnexpectedValueException(format('OPTIONS should be either an array or a Brickrouge\DropDownMenu instance, %type given.', array('type' => gettype($options))));
		}

		return $options;
	}
}