<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge;

/**
 * @var string The ROOT directory of the Brickrouge framework.
 */
defined('Brickrouge\ROOT') or define('Brickrouge\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * @var string Path to the Brickrouge's assets directory.
 */
define('Brickrouge\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/**
 * @var string Version string of the Brickrouge framework.
 */
define('Brickrouge\VERSION', '1.0.0-dev (2011-11-06)');

/**
 * @var string Charset used by the Brickrouge framework.
 */
if (!defined('Brickrouge\CHARSET'))
{
	define('Brickrouge\CHARSET', 'utf-8');
}

/**
 * @var string The DOCUMENT_ROOT directory used by the Brickrouge framework.
 */
if (!defined('Brickrouge\DOCUMENT_ROOT'))
{
	if (defined('ICanBoogie\DOCUMENT_ROOT'))
	{
		define('Brickrouge\DOCUMENT_ROOT', \ICanBoogie\DOCUMENT_ROOT);
	}
	else
	{
		define('Brickrouge\DOCUMENT_ROOT', rtrim($_SERVER['DOCUMENT_ROOT'], DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
	}
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
	Patchable::$callback_translate = 'ICanBoogie\I18n::translate';
	Patchable::$callback_render_exception = 'ICanBoogie\Debug::format_alert';

	Patchable::$callback_get_document = function()
	{
		global $core;

		return $core->document;
	};

	Patchable::$callback_check_session = function()
	{
		global $core;

		return $core->session;
	};
}

/*
 * A simple autoloaded is used to autoload Brickrouge classes if the `Brickrouge\AUTOLOAD` constant
 * is defined.
 */

if (defined('Brickrouge\AUTOLOAD'))
{
	spl_autoload_register
	(
		function($name)
		{
			static $index;

			if ($index === null)
			{
				$path = ROOT; // the $path variable is used within the config file
				$config = require $path . 'config/core.php';
				$index = $config['autoload'];
			}

			if (isset($index[$name]))
			{
				require_once $index[$name];
			}
		}
	);
}