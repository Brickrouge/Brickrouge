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
 * An input element of type `text`.
 *
 * One can override the `type` attribute to use a different kind of input, such as `password`.
 */
class Text extends Element
{
	/**
	 * Text inputs—with appended or prepended text—provide an easy way to give more context for
	 * your inputs. Great examples include the `@` sign for Twitter usernames or `€` for finances.
	 *
	 * @var string
	 */
	const ADDON = '#addon';

	/**
	 * Defines the position of the add-on: `before` or `after`, default to `after`.
	 * @var unknown_type
	 */
	const ADDON_POSITION = '#addon-position';

	/**
	 * Construct the element with the following initial attributes:
	 *
	 * - type: 'text'
	 *
	 * @param array $attributes User attributes
	 */
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			'input', $attributes + array
			(
				'type' => 'text'
			)
		);
	}

	/**
	 * Decorates the HTML with the add-on specified with the {@link ADDON} attribute.
	 *
	 * @see Brickrouge.Element::decorate()
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
	 * @return string
	 */
	protected function decorate_with_prepend($html, $addon)
	{
		return '<div class="input-prepend"><span class="add-on">' . $addon . '</span>' . $html . '</div>';
	}

	/**
	 * Append the HTML with the add-on
	 *
	 * @param string $html
	 * @param string $addon
	 */
	protected function decorate_with_append($html, $addon)
	{
		return '<div class="input-append">' . $html . '<span class="add-on">' . $addon . '</span></div>';
	}
}