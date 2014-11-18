<?php

namespace Brickrouge\Renderer;

use Brickrouge\Element;
use Brickrouge\Form;

class Group extends Element
{
	const GROUP_CLASS = '#group-class';

	protected $form;

	/**
	 * Circumvent Element constructor.
	 */
	public function __construct(array $attributes=[])
	{
		parent::__construct('div', $attributes);
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
		$groups = $this->form[self::GROUPS] ?: [];
		$groups = $this->resolve_groups($groups);
		$groups = $this->dispatch_children($groups);

		return implode($groups);
	}

	/**
	 * Normalize former group definition to use {@link Element} attributes.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function normalize_group_attributes(array $attributes=[])
	{
		static $transform = [

			'description' => self::DESCRIPTION,
			'title' => self::LEGEND,
			'weight' => self::WEIGHT

		];

		$normalized_attributes = [];

		foreach ($attributes as $attribute => $value)
		{
			if (isset($transform[$attribute]))
			{
				$attribute = $transform[$attribute];
			}

			$normalized_attributes[$attribute] = $value;
		}

		return $normalized_attributes;
	}

	/**
	 * Resolve a group definition into an {@link Element} instance.
	 *
	 * @param mixed $group
	 *
	 * @return Element
	 */
	protected function resolve_group($group)
	{
		if ($group instanceof Element)
		{
			return $group;
		}

		$constructor = $this[self::GROUP_CLASS] ?: 'Brickrouge\Group';
		$attributes = $this->normalize_group_attributes($group) + [

			self::CHILDREN => []

		];

		return new $constructor($attributes);
	}

	/**
	 * Resolve group definitions into {@link Element} instances and sort them according to their
	 * {@link WEIGHT}.
	 *
	 * @param array $groups
	 *
	 * @return Element[]
	 */
	protected function resolve_groups(array $groups)
	{
		foreach ($groups as $group_id => &$group)
		{
			$group = $this->resolve_group($group);

			if ($group_id && !is_numeric($group_id))
			{
				$group->add_class('group--' . \Brickrouge\normalize($group_id));
			}
		}

		return \ICanBoogie\sort_by_weight($groups, function($v) {

			return $v[self::WEIGHT];

		});
	}

	/**
	 * Dispatch children into groups.
	 *
	 * @param Element[] $groups
	 *
	 * @return Element[]
	 */
	protected function dispatch_children(array $groups)
	{
		foreach ($this->children as $name => $child)
		{
			if (!$child) continue;

			$group_name = $child instanceof Element ? ($child[self::GROUP] ?: 'primary') : 'primary';

			$groups[$group_name]->children[$name] = $child;
		}

		return $groups;
	}
}
