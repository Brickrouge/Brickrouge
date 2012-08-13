<?php

$lib = $path . 'lib' . DIRECTORY_SEPARATOR;
$icanboogie = $path . 'icanboogie' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'Brickrouge\A' => $lib . 'a.php',
		'Brickrouge\Actions' => $lib . 'actions.php',
		'Brickrouge\Alert' => $lib . 'alerts.php',
		'Brickrouge\Button' => $lib . 'button.php',
		'Brickrouge\Date' => $lib . 'date.php',
		'Brickrouge\DateRange' => $lib . 'date-range.php',
		'Brickrouge\DateTime' => $lib . 'date-time.php',
		'Brickrouge\Document' => $lib . 'document.php',
		'Brickrouge\DropdownMenu' => $lib . 'dropdowns.php',
		'Brickrouge\Element' => $lib . 'element.php',
		'Brickrouge\EmptyElementException' => $lib . 'element.php',
		'Brickrouge\File' => $lib . 'file.php',
		'Brickrouge\Form' => $lib . 'form.php',
		'Brickrouge\Group' => $lib . 'group.php',
		'Brickrouge\Pager' => $lib . 'pager.php',
		'Brickrouge\Popover' => $lib . 'popover.php',
		'Brickrouge\PopoverWidget' => $lib . 'popover.php',
		'Brickrouge\Ranger' => $lib . 'ranger.php',
		'Brickrouge\Salutation' => $lib . 'salutation.php',
		'Brickrouge\Searchbox' => $lib . 'searchbox.php',
		'Brickrouge\SplitButton' => $lib . 'dropdowns.php',
		'Brickrouge\Text' => $lib . 'text.php',
		'Brickrouge\Renderer\Simple' => $lib . 'renderer/simple.php',
		'Brickrouge\Validator' => $lib . 'validator.interface.php'
	)
	+ (defined('ICanBoogie\VERSION') ? array() : array
	(
		'ICanBoogie\Errors' => $icanboogie . 'errors.php',
		'ICanBoogie\Object' => $icanboogie . 'object.php'
	)),

	'cache assets' => false
);