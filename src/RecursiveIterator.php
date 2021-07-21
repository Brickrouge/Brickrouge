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
 * An iterator used to traverse {@link Element} descendant in depth.
 */
class RecursiveIterator extends Iterator implements \RecursiveIterator
{
    public function hasChildren()
    {
        $current = $this->current();

        return !empty($current->children);
    }

    public function getChildren()
    {
        $current = $this->current();

        return new static($current);
    }
}
