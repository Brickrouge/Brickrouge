<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element\Form;

use BrickRouge\Element;

class Section extends Element
{
	const T_PANEL_CLASS = '#form-section-panel-class';

	protected static $auto_panelname;

	protected function render_inner_html()
	{
		$rc = null;
		$children = $this->get_ordered_children();

		foreach ($children as $name => $element)
		{
			//wd_log('name: %name, element: %element', array('%name' => $name, '%element' => $element));

			if (!$element)
			{
				continue;
			}

			$context_class = $name ? wd_normalize($name) : ++self::$auto_panelname;

			$class = 'panel panel-' . $context_class . ' ' . (is_object($element) ? $element->get(self::T_PANEL_CLASS) : '');

			$rc .= '<div class="' . rtrim($class) . '">';

			if (is_object($element))
			{
				$label = t($element->get(Element\Form::T_LABEL));

				if ($label)
				{
					if ($label{0} == '.')
					{
						$label = t(substr($label, 1), array(), array('scope' => array('element', 'label')));
					}

					$rc .= '<div class="form-label form-label-' . $context_class . '">';
					$rc .= $label;

					if ($element->get(Element::T_REQUIRED))
					{
						$rc .= ' <sup>*</sup>';
					}

					$rc .= '<span class="separator">&nbsp;:</span>';

					$rc .= '</div>';
				}
			}

			$rc .= '<div class="form-element form-element-' . $context_class . '">';
			$rc .= $element;
			$rc .= '</div>';

			$rc .= '</div>';
		}

		return $rc;
	}
}