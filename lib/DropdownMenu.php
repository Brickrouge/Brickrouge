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
 * A drop down menu element.
 */
class DropdownMenu extends Element
{
	public function __construct(array $attributes=[])
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
				$html .= '<a href="' . escape($key) . '" data-key="' . escape($key) . '">' . (is_string($option) ? escape($option) : $option) . '</a>';
			}

			$html .= '</li>';
		}

		return $html;
	}

	protected function render_class(array $class_names)
	{
		return parent::render_class($class_names + [ 'dropdown-menu' => true ]);
	}
}
