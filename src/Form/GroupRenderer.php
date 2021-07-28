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
use InvalidArgumentException;

use function Brickrouge\normalize;
use function ICanBoogie\sort_by_weight;
use function is_numeric;

/**
 * Renders form's children in groups.
 */
class GroupRenderer extends Element
{
    public const GROUP_CLASS = '#group-class';

    /**
     * Circumvent Element constructor.
     *
     * @inheritDoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct('div', $attributes + [

                self::GROUP_CLASS => Group::class

            ]);
    }

    private Form $form;

    /**
     * Renders form's children in groups.
     */
    public function __invoke(Form $form): ?string
    {
        $this->form = $form;
        $this->children = $form->get_ordered_children();

        foreach ($form as $element) {
            $tag = $element->tag_name;

            if (!in_array($tag, [ 'input', 'select', 'textarea' ])) {
                continue;
            }

            if ($tag == 'input' && in_array($element['type'], [ 'radio', 'checkbox' ])) {
                continue;
            }

            $element->add_class('form-control');
        }

        return $this->render_inner_html();
    }

    /**
     * @inheritDoc
     */
    protected function render_inner_html(): ?string
    {
        $groups = ($this->form[self::GROUPS] ?: []) + [

                'primary' => []

            ];

        $groups = $this->resolve_groups($groups);
        $groups = $this->dispatch_children($groups);

        return implode($groups);
    }

    /**
     * Resolves a group definition into an {@link Element} instance.
     *
     * @param array<string, mixed>|Element $group
     */
    private function resolve_group(Element|array $group): Element
    {
        if ($group instanceof Element) {
            return $group;
        }

        $constructor = $this[self::GROUP_CLASS];

        return new $constructor($this->normalize_group_attributes($group) + [

                self::CHILDREN => []

            ]);
    }

    /**
     * Normalizes former group definition to use {@link Element} attributes.
     *
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    private function normalize_group_attributes(array $attributes = []): array
    {
        static $transform = [

            'description' => self::DESCRIPTION,
            'title' => self::LEGEND,
            'weight' => self::WEIGHT

        ];

        $normalized_attributes = [];

        foreach ($attributes as $attribute => $value) {
            if (isset($transform[$attribute])) {
                $attribute = $transform[$attribute];
            }

            $normalized_attributes[$attribute] = $value;
        }

        return $normalized_attributes;
    }

    /**
     * Resolves group definitions into {@link Element} instances and sort them according to their
     * {@link WEIGHT}.
     *
     * @param array<string, Element> $groups
     *     Where _key_ is a Group identifier and _value_ an Element.
     *
     * @return Element[]
     */
    protected function resolve_groups(array $groups): array
    {
        foreach ($groups as $group_id => &$group) {
            $group = $this->resolve_group($group);

            if ($group_id && !is_numeric($group_id)) {
                $group->add_class('group--' . normalize($group_id));
            }
        }

        return sort_by_weight($groups, fn(Element $v): int|string => $v[self::WEIGHT] ?? 0);
    }

    /**
     * Dispatches children into groups.
     *
     * @param array<string, Element> $groups
     *
     * @return array<string, Element>
     */
    private function dispatch_children(array $groups): array
    {
        foreach ($this->children as $name => $child) {
            if (!$child) {
                continue;
            }

            $group_name = 'primary';

            if ($child instanceof Element && $child[self::GROUP]) {
                $group_name = $child[self::GROUP];
            }

            if (empty($groups[$group_name])) {
                throw new InvalidArgumentException("There is no group with id $group_name.");
            }

            $groups[$group_name]->children[$name] = $child;
        }

        return $groups;
    }
}
