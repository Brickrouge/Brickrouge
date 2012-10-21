<?php

namespace Brickrouge;

$lib = $path . 'lib' . DIRECTORY_SEPARATOR;
$icanboogie = $path . 'icanboogie' . DIRECTORY_SEPARATOR;

return array
(
	__NAMESPACE__ . '\A' => $lib . 'a.php',
	__NAMESPACE__ . '\Actions' => $lib . 'actions.php',
	__NAMESPACE__ . '\Alert' => $lib . 'alerts.php',
	__NAMESPACE__ . '\Button' => $lib . 'button.php',
	__NAMESPACE__ . '\Dataset' => $lib . 'utilities.php',
	__NAMESPACE__ . '\Date' => $lib . 'date.php',
	__NAMESPACE__ . '\DateRange' => $lib . 'date-range.php',
	__NAMESPACE__ . '\DateTime' => $lib . 'date-time.php',
	__NAMESPACE__ . '\Document' => $lib . 'document.php',
	__NAMESPACE__ . '\DropdownMenu' => $lib . 'dropdowns.php',
	__NAMESPACE__ . '\Element' => $lib . 'element.php',
	__NAMESPACE__ . '\ElementIsEmpty' => $lib . 'element.php',
	__NAMESPACE__ . '\File' => $lib . 'file.php',
	__NAMESPACE__ . '\Form' => $lib . 'form.php',
	__NAMESPACE__ . '\Group' => $lib . 'group.php',
	__NAMESPACE__ . '\Iterator' => $lib . 'utilities.php',
	__NAMESPACE__ . '\Modal' => $lib . 'modal.php',
	__NAMESPACE__ . '\Pager' => $lib . 'pager.php',
	__NAMESPACE__ . '\Popover' => $lib . 'popover.php',
	__NAMESPACE__ . '\PopoverWidget' => $lib . 'popover.php',
	__NAMESPACE__ . '\Ranger' => $lib . 'ranger.php',
	__NAMESPACE__ . '\RecursiveIterator' => $lib . 'utilities.php',
	__NAMESPACE__ . '\Salutation' => $lib . 'salutation.php',
	__NAMESPACE__ . '\Searchbox' => $lib . 'searchbox.php',
	__NAMESPACE__ . '\SplitButton' => $lib . 'dropdowns.php',
	__NAMESPACE__ . '\Text' => $lib . 'text.php',
	__NAMESPACE__ . '\Renderer\Simple' => $lib . 'renderer/simple.php',
	__NAMESPACE__ . '\Validator' => $lib . 'validator.interface.php'
)
+ (defined('ICanBoogie\VERSION') ? array() : array
(
	'ICanBoogie\Errors' => $icanboogie . 'errors.php',
	'ICanBoogie\Object' => $icanboogie . 'object.php'
));