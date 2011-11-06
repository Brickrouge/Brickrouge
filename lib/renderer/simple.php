<?php

namespace BrickRouge\Renderer;

use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Group;

class Simple extends Element
{
	protected $form;

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

		foreach ($groups as $group)
		{
			if (empty($group[self::CHILDREN]))
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

		+ $this->form->get(self::GROUPS, array());

// 		self::sort_by($groups, 'weight');

		#
		# dispatch children into groups
		#

		foreach ($this->children as $name => $element)
		{
			$group = is_object($element) ? $element->get(self::GROUP, 'primary') : 'primary';

			$groups[$group][self::CHILDREN][$name] = $element;
		}

		return $groups;
	}

	protected function render_group(array $group)
	{
		$group = new Group
		(
			array
			(
				self::LEGEND => $group['title'],
				self::CHILDREN => $group[self::CHILDREN]
			)
		);

		return (string) $group;
	}
}