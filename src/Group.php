<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

/**
 * A `<FIELDSET>` element with an optional `<LEGEND>` element.
 *
 * The direct children of the element are wrapped in a `DIV.field` element, see the
 * {@link render_child()} method for more information.
 *
 * Localization:
 *
 * - Labels defined using the {@link Group::LABEL} attribute are translated within the
 * 'group.label|element.label' scope.
 * - Legends defined using the {@link LEGEND} attribute are translated within the 'group.legend'
 * scope.
 */
class Group extends Element
{
    public const LABEL = '#group-label';

    /**
     * Creates a `<FIELDSET.group>` element.
     *
     * @inheritDoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct('fieldset', $attributes + [ 'class' => 'group' ]);
    }

    /**
     * Adds the `no-legend` class name if the group has no legend (the {@link LEGEND} attribute
     * is empty).
     *
     * @inheritdoc
     */
    protected function alter_class_names(array $class_names): array
    {
        $name = $this['name'];

        return parent::alter_class_names($class_names) + [

                'form-group' => true,
                'form-group-name' => $name ? 'group--' . normalize($name) : null,
                'no-legend' => !$this[self::LEGEND]

            ];
    }

    /**
     * Overrides the method to render the child in a `<DIV.field>` wrapper:
     *
     * ```html
     * <div class="field [{normalized_field_name}][required]">
     *     [<label for="{element_id}" class="input-label [required]">{element_form_label}</label>]
     *     <div class="input">{child}</div>
     * </div>
     * ```
     *
     * @inheritdoc
     */
    protected function render_child(Element|string $child): string
    {
        $control_group_class = 'form-group';

        $name = $child['name'];

        if ($name) {
            $control_group_class .= ' form-group--' . normalize($name);
        }

        if ($child[self::REQUIRED]) {
            $control_group_class .= ' required';
        }

        $child = clone $child;

        if (in_array($child->tag_name, [ 'input', 'select', 'textarea' ])) {
            if ($child->tag_name == 'input' && !in_array($child['type'], [ 'radio', 'checkbox' ])) {
                $child->add_class('form-control');
            }
        }

        $state = $child[Element::STATE];

        if ($state) {
            $control_group_class .= " has-$state";
            $child = clone $child;
            $child->add_class("form-control-$state");
        }

        $label = $child[Group::LABEL];

        if ($label) {
            if (!($label instanceof Element)) {
                $label = $this->t($label, [], [

                    'scope' => 'group.label',
                    'default' => $this->t($label, [], [ 'scope' => 'element.label' ])

                ]);
            }

            $label = '<label for="' . $child->id . '" class="form-control-label">' . $label . '</label>' . PHP_EOL;
        }

        return <<<EOT
<div class="$control_group_class">
	$label
	$child
</div>
EOT;
    }

    /**
     * Prepends the inner HTML with a description and a legend.
     *
     * If the {@link DESCRIPTION} attribute is defined the HTML is prepend with a
     * `DIV.group-description>DIV.group-description-inner` element. The description is translated
     * within the "group.description" scope. The description is not escaped.
     *
     * If the {@link LEGEND} attribute is defined the HTML is prepend with a `<LEGEND>` element.
     * The legend can be provided as an object in which it is used _as is_, otherwise the legend
     * is translated within the "group.legend" scope, then escaped.
     *
     * The legend element is rendered using the {@link render_group_legend()} method.
     *
     * @inheritdoc
     */
    protected function render_inner_html(): ?string
    {
        $html = parent::render_inner_html();

        if ($html === null) {
            throw new ElementIsEmpty();
        }

        $description = $this[self::DESCRIPTION];

        if ($description) {
            $description = $this->t($description, [], [ 'scope' => 'group.description' ]);
            $html = $this->render_group_description($description) . $html;
        }

        $legend = $this[self::LEGEND];

        if ($legend) {
            if (is_object($legend)) {
                $legend = (string) $legend;
            } else {
                $legend = escape($this->t($legend, [], [ 'scope' => 'group.legend' ]));
            }

            $html = $this->render_group_legend($legend) . $html;
        }

        return $html;
    }

    /**
     * Renders the group legend.
     *
     * @param string $legend The legend to render.
     *
     * @return string a `legend.group-legend` HTML element.
     */
    protected function render_group_legend($legend)
    {
        return '<legend class="group-legend">' . $legend . '</legend>';
    }

    /**
     * Renders the group description
     *
     * @param string $description
     *
     * @return string a `div.group-description>div.group-description-inner` element.
     */
    protected function render_group_description($description)
    {
        return '<div class="group-description"><div class="group-description-inner">' . $description . '</div></div>';
    }

    /**
     * The description decoration is disabled because the {@link DESCRIPTION} attribute is rendered
     * by the {@link render_inner_html()} method to prepend the inner HTML.
     *
     * @inheritdoc
     */
    protected function decorate_with_description(string $html, string $description): string
    {
        return $html;
    }

    /**
     * The legend decoration is disabled because the {@link LEGEND} attribute is rendered
     * by the {@link render_inner_html()} method to prepend the inner HTML.
     *
     * @inheritdoc
     */
    protected function decorate_with_legend(string $html, string $legend): string
    {
        return $html;
    }
}
