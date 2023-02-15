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

use Iterator as SplIterator;

/**
 * An iterator used to traverse {@link Element} descendant.
 *
 * The iterator collects all descendant elements excluding non {@link Element} instances.
 *
 * @implements SplIterator<int|string, Element>
 */
class Iterator implements SplIterator
{
    /**
     * @var array<int|string, Element>
     */
    private array $children;
    private int $left = 0;

    public function __construct(Element $element)
    {
        $children = [];

        foreach ($element->children as $key => $child) {
            if (!$child instanceof Element) {
                continue;
            }

            $children[$key] = $child;
        }

        $this->children = $children;
    }

    public function rewind(): void
    {
        reset($this->children);

        $this->left = count($this->children);
    }

    public function next(): void
    {
        next($this->children);

        $this->left--;
    }

    public function valid(): bool
    {
        return !!$this->left;
    }

    /**
     * @return int|string|null
     */
    public function key(): mixed
    {
        return key($this->children);
    }

    /**
     * @return Element|false
     */
    public function current(): mixed
    {
        return current($this->children);
    }
}
