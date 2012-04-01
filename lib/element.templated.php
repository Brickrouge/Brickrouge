<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Element;

use ICanBoogie\Debug;

use Brickrouge\Element;
use Brickrouge\Form;

class Templated extends Element
{
	protected $template;

	static protected $label_right_separator = '<span class="separator">&nbsp;:</span>';
	static protected $label_left_separator = '<span class="separator">:&nbsp;</span>';

	public function __construct($type, array $tags, $template=null)
	{
		$this->template = $template;

		parent::__construct($type, $tags);
	}

	protected function render_inner_html()
	{
		$replace = array();

		foreach ($this->children as $name => $child)
		{
			if (!$child)
			{
				continue;
			}

			if (!is_object($child))
			{
				Debug::trigger('Child must be an object, given: !child', array('!child' => $child));

				continue;
			}

			#
			# label
			#

			$label = $child[Form::LABEL];

			if ($label)
			{
				if ($label{0} == '.')
				{
					$label = t(substr($label, 1), array(), array('scope' => array('element', 'label')));
				}
				else
				{
					$label = t($label);
				}

				$is_required = $child[self::REQUIRED];

				$child_id = $child->id;

				// TODO: clean up this mess

				$markup_start = '<label';

				if ($is_required)
				{
					$markup_start .= ' class="required mandatory"';
				}

				$markup_start .= ' for="' . $child_id . '">';

				$start =  $is_required ? $markup_start . $label . '&nbsp;<sup>*</sup>' : $markup_start . $label;
				$finish = '</label>';

				/*
				$complement = $child[self::LABEL_COMPLEMENT];

				if ($complement)
				{
					$finish = ' <span class="complement">' . $complement . '</span>' . $finish;
				}
				*/

				$replace['{$' . $name . '.label}'] = $start . $finish;
				$replace['{$' . $name . '.label:}'] = $start . self::$label_right_separator . $finish;
				$replace['{$' . $name . '.:label}'] = $markup_start . self::$label_left_separator . $start . $finish;
			}

			#
			# element
			#

			$replace['{$' . $name . '}'] = (string) $child;
		}

		$contents = strtr($this->template, $replace);

		return $contents;
	}
}