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

			$html .= '<li><a href="#" data-key="' . escape($key) . '">' . (is_string($option) ? escape($option) : $option) . '</a></li>';
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
		parent::__construct
		(
			'class', $attributes + array
			(
				self::INNER_HTML => escape(t($label, array(), array('scope' => 'button')))
			)
		);
	}

	protected function render_inner_html()
	{
		$label = escape(parent::render_inner_html());
		$class = $this->class;

		$options = $this[self::OPTIONS];

		if (is_array($options))
		{
			$options = new DropdownMenu(array(Element::OPTIONS => $options));
		}

		if (!($options instanceof DropdownMenu))
		{
			throw new \UnexpectedValueException(format('OPTIONS should be either an array or a Brickrouge\DropDownMenu instance, %type given.', array('type' => gettype($options))));
		}

		return <<<EOT
<span class="btn $class">$label</span>
<span class="btn dropdown-toggle $class" data-toggle="dropdown"><span class="caret"></span></span>
$options
EOT;
	}

	protected function render_class(array $class_names)
	{
		return parent::render_class($class_names + array('btn-group' => true));
	}
}