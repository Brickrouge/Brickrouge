<?php

$lib = $path . 'lib' . DIRECTORY_SEPARATOR;
$element = $lib . 'element' . DIRECTORY_SEPARATOR;
$document = $lib . 'document' . DIRECTORY_SEPARATOR;
$renderer = $lib . 'renderer' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'BrickRouge\Element' => $element . 'element.php',
		'BrickRouge\A' => $element . 'a.php',
		'BrickRouge\Button' => $element . 'button.php',
		'BrickRouge\Element\Date' => $element . 'date.php',
		'BrickRouge\Element\DateRange' => $element . 'date-range.php',
		'BrickRouge\Element\DateTime' => $element . 'date-time.php',
		'BrickRouge\Element\Form' => $element . 'form.php',
		'BrickRouge\Element\Form\Section' => $element . 'form.section.php',
		'BrickRouge\Element\Form\Templated' => $element . 'form.templated.php',
		'BrickRouge\Element\Group' => $element . 'group.php',
		'BrickRouge\Element\Pager' => $element . 'pager.php',
		'BrickRouge\Element\Ranger' => $element . 'ranger.php',
		'BrickRouge\Element\Salutation' => $element . 'salutation.php',
		'BrickRouge\Element\Searchbox' => $element . 'searchbox.php',
		'BrickRouge\Text' => $element . 'text.php',
		'BrickRouge\Element\Templated' => $element . 'element.templated.php',
		'BrickRouge\Document' => $document . 'document.php',
		'BrickRouge\Document\Hooks' => $document . 'document.hooks.php',
		'BrickRouge\Renderer\Simple' => $renderer . 'simple.php'
	),

	'cache assets' => false
);