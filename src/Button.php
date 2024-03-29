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
 * A `<BUTTON>` element.
 */
class Button extends Element
{
    /**
     * The element is created with the type "button" and a union of the provided attributes and
     * the following values:
     *
     * - `type`: "button"
     * - {@link INNER_HTML}: The translated and escaped label. The label is translated with
     * the "button" scope. If an {@link HTMLString} instance is provided, it is used as is.
     *
     * @param HTMLString|string $label Label of the button (inner text).
     * @param array<string, mixed> $attributes Optional attributes used to create the element.
     */
    public function __construct(HTMLString|string $label, array $attributes = [])
    {
        if (!$label instanceof HTMLString) {
            $label = escape(t($label, [], [ 'scope' => 'button' ]));
        }

        parent::__construct('button', $attributes + [

            'type' => 'button',

            self::INNER_HTML => (string) $label

        ]);
    }

    /**
     * Adds the `btn` class name.
     *
     * @inheritdoc
     */
    protected function alter_class_names(array $class_names): array
    {
        return parent::alter_class_names($class_names) + [
            'btn' => true
        ];
    }
}
