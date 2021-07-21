<?php

use Brickrouge\Element;

echo new Element('div', [

    Element::INNER_HTML => "I'm in a (magic) well",
    Element::CHILDREN => [

        '<span>Me too !</span>',

        new Element('span', [ Element::INNER_HTML => "Me three !" ])

    ],

    'data-type' => 'magic',

    'class' => 'well'

]);
