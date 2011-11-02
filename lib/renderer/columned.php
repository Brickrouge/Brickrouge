<?php

namespace BrickRouge\Renderer;

use BrickRouge\Element;
use BrickRouge\Form;

class Columned extends Element
{
	const T_NO_WRAPPERS = '#2c-no-wrappers';

	public function __construct($type='table', $tags=array())
	{
		#
		# we merge the provided container tags with the default table tags for the table element
		#

		if ($type == 'table')
		{
			$tags += array
			(
				'cellpadding' => 5,
				'cellspacing' => 0,
				'summary' => ''
			);
		}

		parent::__construct($type, $tags);
	}

	public function __invoke(Form $form)
	{
		$this->children = $form->get_ordered_children();
		$this->contents = null;

		return $this->__toString();
	}

	protected function render_inner_html()
	{
		#
		# create the inner HTML of our container
		#

		$rc = null;

		$is_table = ($this->tagName == 'table');
		$has_wrappers = !$this->get(self::T_NO_WRAPPERS);

		foreach ($this->get_ordered_children() as $child)
		{
			#
			# we skip empty children
			#

			if (!$child)
			{
				continue;
			}

			#
			# create child's form label
			#

			$label = null;

			if (is_object($child))
			{
				$child_id = $child->id;

				$text = $child->get(Form::T_LABEL);

				if ($text)
				{
					$is_required = $child->get(self::T_REQUIRED);

					$label .= '<label';

					if ($is_required)
					{
						$label .= ' class="required mandatory"';
					}

					if ($child_id)
					{
						$label .= ' for="' . $child_id . '"';
					}

					$label .= '>';

					$label .= self::translate_label($text);

					if ($is_required)
					{
						$label .= '&nbsp;<sup>*</sup>';
					}

					$label .= '<span class="separator">&nbsp;:</span>';
					$label .= '</label>';

					$complement = $child->get(Form::T_LABEL_COMPLEMENT);

					if ($complement)
					{
						$label .= ' <small class="completion">' . t($complement) . '</small>';
					}
				}
			}

			$name = is_object($child) ? $child->get('name') : null;

			if ($name)
			{
				$name = normalize($name);
			}

			if ($is_table)
			{
				$rc .= '<tr>';
				$rc .= $label ? ('<td class="label">' . $label) : '<td>&nbsp;';
				$rc .= '</td>';
			}
			else if ($label)
			{
				if ($has_wrappers)
				{
					$rc .= '<div class="form-label' . ($name ? ' form-label-' . $name : '') . '">' . $label . '</div>';
				}
				else
				{
					$rc .= $label;
				}
			}

			#
			# element
			#

			if ($is_table)
			{
				$rc .= '<td>';
				$rc .= $child;
				$rc .= '</td>';
				$rc .= '</tr>';
			}
			else if ($child)
			{
				if ($has_wrappers && is_object($child))
				{
					$rc .= '<div class="form-element' . ($name ? ' form-element-' . $name : '') . '">' . $child . '</div>';
				}
				else
				{
					$rc .= $child;
				}
			}
		}

		return $rc;
	}
}