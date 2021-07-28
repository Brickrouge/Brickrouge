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

use Throwable;

/**
 * Decorates the specified component.
 */
abstract class Decorator implements DecoratorInterface
{
    /**
     * The component to decorate.
     *
     * @var Element|string
     */
    protected Element|string $component;

    public function __construct(Element|string $component)
    {
        $this->component = $component;
    }

    /**
     * Renders the component.
     *
     * @return Element|string The component supplied during {@link __construct} is returned as is.
     */
    public function render()
    {
        return $this->component;
    }

    /**
     * Renders the component into a string.
     *
     * The component is rendered by calling the {@link render()} method and casting the result
     * into a string. If an exception is raised during this process, the exception is rendered
     * with the {@link render_exception()} function and the rendered exception is returned.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return (string) $this->render();
        } catch (Throwable $e) {
            return render_exception($e);
        }
    }
}
