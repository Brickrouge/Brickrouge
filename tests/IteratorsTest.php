<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Brickrouge;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\RecursiveIterator;
use PHPUnit\Framework\TestCase;
use RecursiveIteratorIterator;

final class IteratorsTest extends TestCase
{
    /**
     * @var array<mixed>
     */
    private array $children;

    protected function setUp(): void
    {
        $this->children = [

            'one' => new Element('div', [

                Element::CHILDREN => [

                    'one-one' => new Element('div', [

                        Element::CHILDREN => [

                            'one-one-one' => new Element('div')

                        ]
                    ])
                ]
            ]),

            'text0' => 'text0',

            'two' => new Element('div', [

                Element::CHILDREN => [

                    'two-one' => new Element('div', [

                        Element::CHILDREN => [

                            'two-one-one' => new Element('div')

                        ]
                    ]),

                    'two-two' => new Element('div', [

                        Element::CHILDREN => [

                            'two-two-one' => new Element('div'),
                            'text220',
                            'two-two-two' => new Element('div')

                        ]
                    ])
                ]
            ]),

            'text1' => 'text1',

            'three' => new Element('div', [

                Element::CHILDREN => [

                    'three-one' => new Element('div'),
                    'three-two' => new Element('div'),
                    'three-three' => new Element('div', [

                        Element::CHILDREN => [

                            'three-three-one' => new Element('div'),
                            'text330',
                            'three-three-two' => new Element('div'),
                            'text330',
                            'three-three-three' => new Element('div')

                        ]
                    ])
                ]
            ]),

            'text3' => 'text3'
        ];
    }

    public function testElementIterator(): void
    {
        $str = '';
        $element = new Element('div', [ Element::CHILDREN => $this->children ]);

        foreach ($element as $child) {
            $str .= '#' . $child['name'];
        }

        $this->assertEquals('#one#two#three', $str);
    }

    public function testElementRecusiveIterator(): void
    {
        $str = '';
        $element = new Element('div', [ Element::CHILDREN => $this->children ]);
        $iterator = new RecursiveIteratorIterator(
            new RecursiveIterator($element),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $child) {
            $str .= '#' . $child['name'];
        }

        $this->assertEquals(
            '#one#one-one#one-one-one#two#two-one#two-one-one#two-two#two-two-one#two-two-two#'
            . 'three#three-one#three-two#three-three#three-three-one#three-three-two#three-three-three',
            $str
        );
    }

    public function testFormIterator(): void
    {
        $str = '';
        $element = new Form([ Element::CHILDREN => $this->children ]);

        foreach ($element as $child) {
            $str .= '#' . $child['name'];
        }

        $this->assertNotEquals('#one#two#three', $str);
    }

    public function testFormRecusiveIterator(): void
    {
        $str = '';
        $element = new Form([ Element::CHILDREN => $this->children ]);

        foreach ($element as $child) {
            $str .= '#' . $child['name'];
        }

        $this->assertEquals(
            '#one#one-one#one-one-one#two#two-one#two-one-one#two-two#two-two-one#two-two-two'
            . '#three#three-one#three-two#three-three#three-three-one#three-three-two#three-three-three',
            $str
        );
    }
}
