<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Form;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;

/**
 * Renders form's children in groups.
 */
class GroupRenderer extends Element
{
	const GROUP_CLASS = '#group-class';

	/**
	 * @var Form
	 */
	protected $form;

	/**
	 * Circumvent Element constructor.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [

			self::GROUP_CLASS => Group::class

		]);
	}

	/**
	 * Renders form's children in groups.
	 *
	 * @param Form $form
	 *
	 * @return string
	 */
	public function __invoke(Form $form)
	{
		$this->form = $form;
		$this->children = $form->get_ordered_children();

		return $this->render_inner_html();
	}

	/**
	 * @inheritdoc
	 */
	protected function render_inner_html()
	{
		$groups = ($this->form[self::GROUPS] ?: []) + [

			'primary' => []

		];

		$groups = $this->resolve_groups($groups);
		$groups = $this->dispatch_children($groups);

		return implode($groups);
	}

	/**
	 * Normalizes former group definition to use {@link Element} attributes.
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	protected function normalize_group_attributes(array $attributes = [])
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
	 * Resolves a group definition into an {@link Element} instance.
	 *
	 * @param Element|array $group
	 *
	 * @return Element
	 */
	protected function resolve_group($group)
	{
		if ($group instanceof Element)
		{
			return $group;
		}

		$constructor = $this[self::GROUP_CLASS];

		return new $constructor($this->normalize_group_attributes($group) + [

			self::CHILDREN => []

		]);
	}

	/**
	 * Resolves group definitions into {@link Element} instances and sort them according to their
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
	 * Dispatches children into groups.
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

			$group_name = 'primary';

			if ($child instanceof Element && $child[self::GROUP])
			{
				$group_name = $child[self::GROUP];
			}

			if (empty($groups[$group_name]))
			{
				throw new \InvalidArgumentException("There is no group with id $group_name.");
			}

			$groups[$group_name]->children[$name] = $child;
		}

		return $groups;
	}
}
