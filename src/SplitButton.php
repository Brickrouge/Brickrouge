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

use UnexpectedValueException;

/**
 * An element made of a button and a drop-down menu.
 */
class SplitButton extends Element
{
    public function __construct(string $label, array $attributes = [])
    {
        $label = escape(t($label, [], [ 'scope' => 'button' ]));

        parent::__construct('div', $attributes + [

                self::INNER_HTML => $label

            ]);
    }

    /**
     * Renders the button and drop-down trigger button.
     *
     * The `btn-*` class names are forwarded to the buttons.
     */
    protected function render_inner_html(): ?string
    {
        $label = parent::render_inner_html();
        $class = parent::render_class(array_filter(
            $this->class_names,
            fn($class_name) => str_starts_with($class_name, 'btn-') || $class_name === 'disabled',
            ARRAY_FILTER_USE_KEY
        ));

        return $this->render_splitbutton_label($label, $class)
            . $this->render_splitbutton_toggle($class)
            . $this->resolve_options($this[self::OPTIONS]);
    }

    /**
     * Removes the `btn-*` class names and adds the `btn-group` class.
     */
    protected function render_class(array $class_names): string
    {
        return parent::render_class(
            array_filter(
                $class_names,
                fn($class_name) => !str_contains($class_name, 'btn-'),
                ARRAY_FILTER_USE_KEY
            )
            + [ 'btn-group' => true ]
        );
    }

    /**
     * Renders the button part of the element.
     *
     * @param string $label Label of the button. The label is already a HTML string. It doesn't
     * need to be escaped.
     * @param string $class Class of the label.
     *
     * @return string A HTML string.
     */
    protected function render_splitbutton_label(string $label, string $class): string
    {
        return <<<EOT
<button class="btn $class">$label</button>
EOT;
    }

    /**
     * Renders the drop down toggle part of the element.
     *
     * @param string $class Class of the element.
     *
     * @return string A HTML string.
     */
    protected function render_splitbutton_toggle(string $class): string
    {
        return <<<EOT
<button class="btn dropdown-toggle $class" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button>
EOT;
    }

    /**
     * Resolves the provided options into a {@link DropdownMenu} element.
     *
     * @param array<int|string, string>|DropdownMenu $options
     *
     * @return DropdownMenu
     *
     * @throws UnexpectedValueException If the provided options cannot be resolved into a
     * {@link DropdownMenu} element.
     */
    private function resolve_options(DropdownMenu|array $options): DropdownMenu
    {
        if (is_array($options)) {
            $options = new DropdownMenu([

                Element::OPTIONS => $options,

                'value' => $this['value'] ?? $this[self::DEFAULT_VALUE]

            ]);
        }

        return $options;
    }
}
