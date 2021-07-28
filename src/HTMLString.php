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
 * Representation of an HTML string.
 *
 * An HTML string is considered safe to use and is not escaped after it has been rendered.
 */
final class HTMLString implements HTMLStringInterface
{
    public function __construct(
        private string $html
    ) {
    }

    public function render(): string
    {
        return $this->html;
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (Throwable $e) {
            return render_exception($e);
        }
    }
}
