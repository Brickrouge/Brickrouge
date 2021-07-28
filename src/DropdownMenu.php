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
 * A drop down menu element.
 */
class DropdownMenu extends Element
{
    public function __construct(array $attributes = [])
    {
        parent::__construct('div', $attributes);
    }

    /**
     * @inheritdoc
     */
    protected function render_inner_html(): ?string
    {
        $html = '';
        $options = $this[self::OPTIONS];
        $selected = $this['value'];

        if ($selected === null) {
            $selected = $this[self::DEFAULT_VALUE];
        }

        foreach ($options as $key => $item) {
            if ($item === false) {
                $html .= $this->render_dropdown_divider();

                continue;
            } elseif ($item === null) {
                continue;
            }

            $html .= $this->render_dropdown_item($key, $item, $selected);
        }

        return $html;
    }

    /**
     * Renders dropdown item.
     */
    private function render_dropdown_item(int|string $key, Element|string $item, int|string|null $selected): Element
    {
        if (!$item instanceof Element) {
            $item = new A($item, is_string($key) && $key !== "" ? $key : '#');
        }

        $item['data-key'] = $key;
        $item->add_class('dropdown-item');

        if ($selected !== null & (string) $key === (string) $selected) {
            $item->add_class('active');
        }

        return $item;
    }

    /**
     * Renders dropdown divider.
     */
    private function render_dropdown_divider(): string
    {
        return <<<EOT
<div class="dropdown-divider"></div>
EOT;
    }

    /**
     * Adds the `dropdown-menu` class.
     *
     * @inheritDoc
     */
    protected function render_class(array $class_names): string
    {
        return parent::render_class($class_names + [ 'dropdown-menu' => true ]);
    }
}
