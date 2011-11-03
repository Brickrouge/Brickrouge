<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

/**
 * @var string The ROOT directory of the BrickRouge framework.
 */
define('BrickRouge\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * @var string Path to the BrickRouge's assets directory.
 */
define('BrickRouge\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/**
 * @var string Version string of the BrickRouge framework.
 */
define('BrickRouge\VERSION', '1.0.0-dev (2011-11-02)');

/**
 * @var string Charset used by the BrickRouge framework.
 */
if (!defined('BrickRouge\CHARSET'))
{
	define('BrickRouge\CHARSET', 'utf-8');
}

/*
 * Helpers
 */
require_once ROOT . 'lib/helpers.php';

/*
 * If the ICanBoogie framework is available we patch some of our functions to use his.
 */
if (defined('ICanBoogie\VERSION'))
{
	Patchable::$callback_translate = '\ICanBoogie\I18n::translate';
	Patchable::$callback_get_document = function()
	{
		global $core;

		return $core->document;
	};
}