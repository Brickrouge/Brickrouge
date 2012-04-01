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
 * Version string of the Brickrouge framework.
 *
 * @var string
 */
define('Brickrouge\VERSION', '1.0.0-wip (2012-04-01)');

/**
 * The ROOT directory of the Brickrouge framework.
 *
 * @var string
 */
defined('Brickrouge\ROOT') or define('Brickrouge\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * The DOCUMENT_ROOT directory used by the Brickrouge framework.
 *
 * @var string
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

/**
 * Path to the Brickrouge's assets directory.
 *
 * @var string
 */
define('Brickrouge\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/**
 * Path to the directory used to stored files which are not web accessible, such as the assets in
 * the Phar. The {@link Document::resolve_url()} method use this directory to make files
 * files web accessible.
 *
 * @var string
 */
defined('Brickrouge\ACCESSIBLE_ASSETS') or define('Brickrouge\ACCESSIBLE_ASSETS', DOCUMENT_ROOT . 'public' . DIRECTORY_SEPARATOR . 'brickrouge' . DIRECTORY_SEPARATOR);

/**
 * Charset used by the Brickrouge framework.
 *
 * @var string
 */
if (!defined('Brickrouge\CHARSET'))
{
	define('Brickrouge\CHARSET', 'utf-8');
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