<?php

$lib = $path . 'lib' . DIRECTORY_SEPARATOR;
$icanboogie = $path . 'icanboogie' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'BrickRouge\A' => $lib . 'a.php',
		'BrickRouge\AlertMessage' => $lib . 'alert-message.php',
		'BrickRouge\Button' => $lib . 'button.php',
		'BrickRouge\Date' => $lib . 'date.php',
		'BrickRouge\DateRange' => $lib . 'date-range.php',
		'BrickRouge\DateTime' => $lib . 'date-time.php',
		'BrickRouge\Document' => $lib . 'document.php',
		'BrickRouge\Element' => $lib . 'element.php',
		'BrickRouge\File' => $lib . 'file.php',
		'BrickRouge\Form' => $lib . 'form.php',
		'BrickRouge\Form\Templated' => $lib . 'form.templated.php',
		'BrickRouge\Group' => $lib . 'group.php',
		'BrickRouge\Pager' => $lib . 'pager.php',
		'BrickRouge\Popover' => $lib . 'popover.php',
		'BrickRouge\PopoverWidget' => $lib . 'popover.php',
		'BrickRouge\Ranger' => $lib . 'ranger.php',
		'BrickRouge\Salutation' => $lib . 'salutation.php',
		'BrickRouge\Searchbox' => $lib . 'searchbox.php',
		'BrickRouge\Text' => $lib . 'text.php',
		'BrickRouge\Element\Templated' => $lib . 'element.templated.php',
		'BrickRouge\Renderer\Simple' => $lib . 'renderer/simple.php',
		'BrickRouge\Validator' => $lib . 'validator.interface.php'
	)
	+ (defined('ICanBoogie\VERSION') ? array() : array
	(
		'ICanBoogie\Errors' => $icanboogie . 'errors.php',
		'ICanBoogie\Object' => $icanboogie . 'object.php'
	)),

	'cache assets' => false
);