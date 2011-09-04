<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element;

use BrickRouge\Element;

class Group extends Element
{
	public function __construct($tags)
	{
		parent::__construct('fieldset', $tags);
	}

	protected function render_child($child)
	{
		$row_class = 'field';

		$name = $child->get('name');

		if ($name)
		{
			$row_class .= ' field--' . wd_normalize($name);
		}

		$label = $child->get(Element\Form::T_LABEL);

		if ($label)
		{
			$label = t($label, array(), array('scope' => array('element', 'label')));

			$label_class = 'input-label';

			if ($child->get(self::T_REQUIRED))
			{
				$row_class .= ' required';
				$label_class .= ' required';
			}

			$label = '<label for="' . $child->id . '" class="' . $label_class . '">' . wd_entities($label) . '</label>';
		}

		if ($child->has_class('error'))
		{
			$row_class .= ' error';
		}

		return <<<EOT
<div class="$row_class">
	$label
	<div class="input">$child</div>
</div>
EOT;
	}

	protected function render_inner_html()
	{
		$rc = '';

		$legend = $this->get(self::T_LEGEND);

		if ($legend)
		{
			$rc .= '<legend>' . (is_object($legend) ? (string) $legend : wd_entities($legend)) . '</legend>';
		}

		return $rc . parent::render_inner_html();
	}

	/**
	 * The legend decoration is disabled because the T_LEGEND attribute is handled during
	 * {@link render_inner_html()}.
	 *
	 * @see BrickRouge.Element::decorate_with_legend()
	 */
	protected function decorate_with_legend($html, $legend)
	{
		return $html;
	}
}