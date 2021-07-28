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
 * A group that lay its children into columns.
 */
class ColumnedGroup extends Group
{
    /**
     * Defines the number of columns.
     */
    public const COLUMNS = '#columned-group-columns';

    /**
     * Defines the total number of columns.
     */
    public const COLUMNS_TOTAL = '#columned-group-columns-total';

    /**
     * The instance is constructed with the following defaults:
     *
     * - {@link COLUMNS}: 3.
     * - {@link COLUMNS_TOTAL}: 12.
     *
     * @inheritDoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes + [

                self::COLUMNS => 3,
                self::COLUMNS_TOTAL => 12

            ]);
    }

    /**
     * Dispatch children into columns and render them.
     */
    protected function render_inner_html(): ?string
    {
        $columns = [];
        $children = $this->ordered_children;
        $columns_n = $this[self::COLUMNS];
        $i = 0;

        foreach ($children as $child_id => $child) {
            $column_i = $i++ % $columns_n;
            $columns[$column_i][$child_id] = $child;
        }

        $rendered_columns = [];

        foreach ($columns as $column) {
            $rendered_columns[] = $this->render_group_column($column);
        }

        return new Element('div', [

            Element::CHILDREN => $rendered_columns,

            'class' => "row"

        ]);
    }

    /**
     * Render a group column.
     *
     * @param array<string, Element|string> $column
     */
    private function render_group_column(array $column): Element
    {
        $w = $this[self::COLUMNS_TOTAL] / $this[self::COLUMNS];

        return new Element('div', [

            Element::INNER_HTML => $this->render_children($column),

            'class' => "col-md-$w span-$w"

        ]);
    }
}
