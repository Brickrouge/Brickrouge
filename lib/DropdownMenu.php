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
	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes);
	}

	/**
	 * @inheritdoc
	 */
	protected function render_inner_html()
	{
		$html = '';
		$options = $this[self::OPTIONS];
		$selected = $this['value'];

		if ($selected === null)
		{
			$selected = $this[self::DEFAULT_VALUE];
		}

		foreach ($options as $key => $item)
		{
			if ($item === false)
			{
				$html .= $this->render_dropdown_divider();

				continue;
			}
			else if ($item === null)
			{
				continue;
			}

			$html .= $this->render_dropdown_item($key, $item, $selected);
		}

		return $html;
	}

	/**
	 * Renders dropdown item.
	 *
	 * @param string|int $key
	 * @param Element|string $item
	 * @param mixed $selected
	 *
	 * @return A|Element
	 */
	protected function render_dropdown_item($key, $item, $selected)
	{
		if (!$item instanceof Element)
		{
			$item = new A($item, is_numeric($key) ? '#' : $key);
		}

		$item['data-key'] = $key;
		$item->add_class('dropdown-item');

		if ((string) $key === (string) $selected)
		{
			$item->add_class('active');
		}

		return $item;
	}

	/**
	 * Renders dropdown divider.
	 *
	 * @return string
	 */
	protected function render_dropdown_divider()
	{
		return <<<EOT
<div class="dropdown-divider"></div>
EOT;
	}

	/**
	 * Adds the `dropdown-menu` class.
	 *
	 * @inheritdoc
	 */
	protected function render_class(array $class_names)
	{
		return parent::render_class($class_names + [ 'dropdown-menu' => true ]);
	}
}
