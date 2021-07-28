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
 * A popover element with automatically attached behaviour.
 */
class PopoverWidget extends Popover
{
    /**
     * Whether the widget should be made visible once elements are ready.
     */
    public const VISIBLE = '#visible';

    /**
     * Overrides the {@link Popover} initial attribute `class` with the value
     * "widget-popover popover". The "widget-popover" class is used to automatically attach
     * popover behaviour to the element, while the "popover" class is used to style the element.
     *
     * If you override the `class` attribute, remember to define this two class names, unless
     * you want to use a different behaviour or style.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes + [

                self::IS => 'Popover',

                'class' => 'widget-popover popover'

            ]);
    }

    /**
     * Adds the `visible` property to the dataset.
     */
    protected function alter_dataset(array $dataset): array
    {
        return parent::alter_dataset($dataset + [

                'visible' => $this[self::VISIBLE]

            ]);
    }
}
