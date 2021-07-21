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

use function get_debug_type;

/**
 * An element made of a button and a drop down menu.
 */
class SplitButton extends Element
{
    /**
     * @param string $label
     * @param array $attributes
     */
    public function __construct($label, array $attributes = [])
    {
        if (is_string($label)) {
            $label = escape(t($label, [], [ 'scope' => 'button' ]));
        }

        parent::__construct('div', $attributes + [

                self::INNER_HTML => $label

            ]);
    }

    /**
     * Renders the button and drop down trigger button.
     *
     * The `btn-*` class names are forwarded to the buttons.
     *
     * @inheritdoc
     */
    protected function render_inner_html()
    {
        $label = parent::render_inner_html();
        $class = parent::render_class(array_filter($this->class_names, function ($class_name) {
            return strpos($class_name, 'btn-') === 0 || $class_name === 'disabled';
        }, ARRAY_FILTER_USE_KEY));

        return $this->render_splitbutton_label($label, $class)
            . $this->render_splitbutton_toggle($class)
            . $this->resolve_options($this[self::OPTIONS]);
    }

    /**
     * Removes the `btn-*` class names and adds the `btn-group` class.
     *
     * @inheritdoc
     */
    protected function render_class(array $class_names)
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
    protected function render_splitbutton_label($label, $class)
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
    protected function render_splitbutton_toggle($class)
    {
        return <<<EOT
<button class="btn dropdown-toggle $class" data-toggle="dropdown"><span class="sr-only">Toggle Dropdown</span></button>
EOT;
    }

    /**
     * Resolves the provided options into a {@link DropdownMenu} element.
     *
     * @param mixed $options
     *
     * @return DropdownMenu
     *
     * @throws UnexpectedValueException If the provided options cannot be resolved into a
     * {@link DropdownMenu} element.
     */
    protected function resolve_options($options)
    {
        if (is_array($options)) {
            $options = new DropdownMenu([

                Element::OPTIONS => $options,

                'value' => $this['value'] ?: $this[self::DEFAULT_VALUE]

            ]);
        }

        if (!$options instanceof DropdownMenu) {
            throw new UnexpectedValueException(format(
                'OPTIONS should be either an array or a Brickrouge\DropDownMenu instance, %type given.',
                [ 'type' => get_debug_type($options) ]
            ));
        }

        return $options;
    }
}
