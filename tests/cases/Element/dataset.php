<?php

namespace Brickrouge;

echo new Element('div', [

    Element::INNER_HTML => '',

    'class' => 'testing',

    'data-one' => 'one',
    'data-two' => 'two',
    'data-three' => 'three',
    'data-true' => true,
    'data-false' => false,
    'data-null' => null,
    'data-numeric' => 1

]);
