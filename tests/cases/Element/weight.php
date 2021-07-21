<?php

namespace Brickrouge;

echo new Element('div', [

    Element::CHILDREN => [

        'two' => new Text([ Element::WEIGHT => 1 ]),
        'one' => new Text([ Element::WEIGHT => 0 ]),
        'four' => new Text([ Element::WEIGHT => 'bottom' ]),
        'three' => new Text([ Element::WEIGHT => 'before:four' ]),
        'zero' => new Text([ Element::WEIGHT => 'top' ]),

    ]

]);
