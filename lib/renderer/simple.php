<?php

namespace BrickRouge\Renderer;

use BrickRouge\Element\Group;

use BrickRouge\Element;

class Simple extends Element
{
	protected $form;

	public function __construct()
	{

	}

	public function __invoke(Element\Form $form)
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

		foreach ($groups as $group)
		{
			if (empty($group[self::T_CHILDREN]))
			{
				continue;
			}

			$rc .= PHP_EOL . $this->render_group($group) . PHP_EOL;
		}

		return $rc;
	}

	protected function group_children()
	{
		$groups = array
		(
			'primary' => array
			(
				'title' => ''
			)
		)

		+ $this->form->get(self::T_GROUPS, array());

// 		self::sort_by($groups, 'weight');

		#
		# dispatch children into groups
		#

		foreach ($this->children as $name => $element)
		{
			$group = is_object($element) ? $element->get(self::T_GROUP, 'primary') : 'primary';

			$groups[$group][self::T_CHILDREN][$name] = $element;
		}

		return $groups;
	}

	protected function render_group(array $group)
	{
		$group = new Group
		(
			array
			(
				self::T_LEGEND => $group['title'],
				self::T_CHILDREN => $group[self::T_CHILDREN]
			)
		);

		return (string) $group;
	}
}