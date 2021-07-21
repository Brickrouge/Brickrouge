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
 * An interface to a _Decorator_ design pattern used to decorate HTML strings and objects that
 * render as HTML strings.
 */
interface DecoratorInterface
{
    /**
     * Renders the component.
     */
    public function render();

    /**
     * Renders the component into a string.
     */
    public function __toString();
}
