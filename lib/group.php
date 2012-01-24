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
 * Creates a FIELDSET element with an optional LEGEND element.
 *
 * The direct children of the element are wrapped in a DIV.field element, see the
 * {@link render_child()} method for more information.
 */
class Group extends Element
{
	/**
	 * Constructor.
	 *
	 * Create an element with the type "fieldset".
	 *
	 * @param array $tags
	 */
	public function __construct(array $tags=array())
	{
		parent::__construct('fieldset', $tags);
	}

	/**
	 * Override the method to render the child in a DIV.field wrapper.
	 *
	 * <div class="field [{normalized_field_name}][{required}]">
	 *     [<label for="{element_id}" class="input-label {required}">{element_form_label}</label>]
	 *     <div class="input">{child}</div>
	 * </div>
	 *
	 * @see Brickrouge.Element::render_child()
	 */
	protected function render_child($child)
	{
		$field_class = 'field';

		$name = $child['name'];

		if ($name)
		{
			$field_class .= ' field--' . normalize($name);
		}

		$label = $child[Form::LABEL];

		if ($label)
		{
			$label = t($label, array(), array('scope' => array('element.label')));

			$label_class = 'input-label';

			if ($child[self::REQUIRED])
			{
				$field_class .= ' required';
				$label_class .= ' required';
			}

			$label = '<label for="' . $child->id . '" class="' . $label_class . '">' . escape($label) . '</label>' . PHP_EOL;
		}

		$state = $child[Element::STATE];

		if ($state)
		{
			$field_class .= ' ' . $state;
		}

		return <<<EOT
<div class="$field_class">
	$label<div class="input">$child</div>
</div>
EOT;
	}

	/**
	 * Prepend the inner HTML with a LEGEND element if the {@link LEGEND} tag is not empty.
	 *
	 * The legend is translated with the "element.legend" scope.
	 *
	 * @see Brickrouge.Element::render_inner_html()
	 */
	protected function render_inner_html()
	{
		$rc = '';

		$legend = $this[self::LEGEND];

		if ($legend)
		{
			$legend = t($legend, array(), array('scope' => 'element.legend'));
			$rc .= '<legend>' . (is_object($legend) ? (string) $legend : escape($legend)) . '</legend>';
		}

		return $rc . parent::render_inner_html();
	}

	/**
	 * The legend decoration is disabled because the {@link LEGEND} tag is already used by the
	 * {@link render_inner_html()} method to prepend the inner HTML.
	 *
	 * @see Brickrouge.Element::decorate_with_legend()
	 */
	protected function decorate_with_legend($html, $legend)
	{
		return $html;
	}
}