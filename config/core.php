<?php

$lib = $path . 'lib' . DIRECTORY_SEPARATOR;
$element = $lib . 'element' . DIRECTORY_SEPARATOR;
$document = $lib . 'document' . DIRECTORY_SEPARATOR;
$renderer = $lib . 'renderer' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'BrickRouge\A' => $element . 'a.php',
		'BrickRouge\Button' => $element . 'button.php',
		'BrickRouge\Date' => $element . 'date.php',
		'BrickRouge\DateRange' => $element . 'date-range.php',
		'BrickRouge\DateTime' => $element . 'date-time.php',
		'BrickRouge\Document' => $document . 'document.php',
		'BrickRouge\Document\Hooks' => $document . 'document.hooks.php',
		'BrickRouge\Element' => $element . 'element.php',
		'BrickRouge\Form' => $lib . 'form.php',
		'BrickRouge\Form\Templated' => $element . 'form.templated.php',
		'BrickRouge\Group' => $element . 'group.php',
		'BrickRouge\Pager' => $element . 'pager.php',
		'BrickRouge\Ranger' => $element . 'ranger.php',
		'BrickRouge\Salutation' => $element . 'salutation.php',
		'BrickRouge\Searchbox' => $element . 'searchbox.php',
		'BrickRouge\Section' => $element . 'section.php',
		'BrickRouge\Text' => $element . 'text.php',
		'BrickRouge\Element\Templated' => $element . 'element.templated.php',
		'BrickRouge\Renderer\Simple' => $renderer . 'simple.php'
	),

	'cache assets' => false
);