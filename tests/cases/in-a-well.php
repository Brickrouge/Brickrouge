<?php

use Brickrouge\Element;

echo new Element('div', [

    Element::INNER_HTML => "I'm in a (magic) well",

    'data-type' => 'magic',

    'class' => 'well'

]);
