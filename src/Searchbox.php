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

class Searchbox extends Element
{
    /**
     * @var array<string, Element>
     *
     * @phpstan-ignore-next-line
     */
    private array $elements = [];

    public function __construct(array $attributes = [])
    {
        parent::__construct('div', $attributes + [

                self::CHILDREN => [

                    'q' => $this->elements['q'] = new Text(),

                    $this->elements['trigger'] = new Button('Search', [

                        'type' => 'submit'

                    ])
                ],

                'class' => 'widget-searchbox'

            ]);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (in_array($offset, [ 'name', 'value', 'placeholder' ])) {
            $this->elements['q'][$offset] = $value;
        }

        parent::offsetSet($offset, $value);
    }
}
