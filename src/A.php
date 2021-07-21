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
 * An `<A>` element.
 *
 * ```php
 * <?php
 *
 * use Brickrouge\A;
 *
 * echo new A('Brickrouge', 'http://brickrouge.org');
 * ```
 */
class A extends Element
{
    /**
     * @param string|Element $label Defines the content of the element. If `$label` is not
     * a {@link Element} instance it is escaped.
     * @param string $href URI for linked resource.
     * @param array $attributes Optional attributes.
     */
    public function __construct($label, $href = '#', array $attributes = [])
    {
        if (!($label instanceof HTMLStringInterface)) {
            $label = escape(t($label));
        }

        parent::__construct('a', $attributes + [

                'href' => $href,

                self::INNER_HTML => $label

            ]);
    }
}
