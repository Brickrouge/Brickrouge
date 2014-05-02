<?php

namespace Brickrouge;

echo new Element('input', [

	Element::LABEL => 0,

	'type' => 'checkbox'

]);

echo "\n";

echo new Element('input', [

	Element::LABEL => null,

	'type' => 'checkbox'

]);

echo "\n";

echo new Element('input', [

	Element::LABEL => false,

	'type' => 'checkbox'

]);

echo "\n";

echo new Element('input', [

	Element::LABEL => '',

	'type' => 'checkbox'

]);