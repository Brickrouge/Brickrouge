<?php

namespace Brickrouge\Renderer;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;

class Simple extends Element
{
	protected $form;

	/**
	 * Circumvent Element constructor.
	 */
	public function __construct()
	{

	}

	public function __invoke(Form $form)
	{
		$this->form = $form;
		$this->children = $form->get_ordered_children();
		$this->contents = null;

		return $this->render_inner_html();
	}

	protected function render_inner_html()
	{
		$rc = '';
		$groups = $this->group_children();

		foreach ($groups as $key => $group)
		{
			if (empty($group[self::CHILDREN]))
			{
				continue;
			}

			$rc .= PHP_EOL . $this->render_group($group, $key) . PHP_EOL;
		}

		return $rc;
	}

	protected function group_children()
	{
		$groups = $this->form[self::GROUPS] ?: array();

		\Brickrouge\stable_sort($groups, function($v) { return isset($v['weight']) ? $v['weight'] : 0; });

		#
		# dispatch children into groups
		#

		foreach ($this->children as $name => $element)
		{
			if (!$element) continue;

			$group = is_object($element) ? ($element[self::GROUP] ?: 'primary') : 'primary';

			$groups[$group][self::CHILDREN][$name] = $element;
		}

		return $groups;
	}

	protected function render_group(array $group, $key)
	{
		$class = isset($group['class']) ? $group['class'] : null;

		if ($key && !is_numeric($key))
		{
			$class .= ' group--' . \Brickrouge\normalize($key);
		}

		$group = new Group
		(
			array
			(
				self::CHILDREN => $group[self::CHILDREN],
				self::DESCRIPTION => isset($group['description']) ? $group['description'] : null,
				self::LEGEND => isset($group['title']) ? $group['title'] : null,

				'class' => $class
			)
		);

		return (string) $group;
	}
}