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
 * An `<INPUT>` element of type `text`.
 *
 * One can override the `type` attribute to use a different kind of input, such as `password`.
 */
class Text extends Element
{
	/**
	 * Text inputs—with appended or prepended text—provide an easy way to give more context for
	 * your inputs. Great examples include the `@` sign for Twitter usernames or `€` for finances.
	 */
	const ADDON = '#addon';

	/**
	 * Defines the position of the add-on: `before` or `after`. Defaults to `after`.
	 */
	const ADDON_POSITION = '#addon-position';

	/**
	 * Construct the element with the following initial attributes:
	 *
	 * - type: 'text'
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct('input', $attributes + [

			'type' => 'text'

		]);
	}

	/**
	 * Renders the addon.
	 *
	 * @param mixed $addon
	 *
	 * @return string|Element
	 */
	protected function render_addon($addon)
	{
		if ($addon instanceof Button || $addon instanceof DropdownMenu)
		{
			return $addon;
		}

		return <<<EOT
<span class="add-on">{$addon}</span>
EOT;
	}

	/**
	 * Decorates the HTML with the add-on specified with the {@link ADDON} attribute.
	 *
	 * @inheritdoc
	 */
	protected function decorate($html)
	{
		$addon = $this[self::ADDON];

		if ($addon)
		{
			if ($this[self::ADDON_POSITION] == 'before')
			{
				$html = $this->decorate_with_prepend($html, $addon);
			}
			else
			{
				$html = $this->decorate_with_append($html, $addon);
			}
		}

		return parent::decorate($html);
	}

	/**
	 * Prepend the HTML with the add-on.
	 *
	 * @param string $html
	 * @param string $addon
	 *
	 * @return string The decorated HTML.
	 */
	protected function decorate_with_prepend($html, $addon)
	{
		return '<div class="input-prepend">' . $this->render_addon($addon) . $html . '</div>';
	}

	/**
	 * Append the HTML with the add-on
	 *
	 * @param string $html
	 * @param string $addon
	 *
	 * @return string The decorated HTML.
	 */
	protected function decorate_with_append($html, $addon)
	{
		return '<div class="input-append">' . $html . $this->render_addon($addon) . '</div>';
	}
}
