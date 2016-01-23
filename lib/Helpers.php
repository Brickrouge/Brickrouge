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

use ICanBoogie\Errors;
use Brickrouge\Helpers\PublishAssets;

/**
 * Brickrouge helpers.
 *
 * The following helpers are patchable:
 *
 * - {@link format()}
 * - {@link format_size()}
 * - {@link get_accessible_file()}
 * - {@link get_document()}
 * - {@link normalize()}
 * - {@link render_exception()}
 * - {@link t()}
 *
 * @method static string format() format(string $str, array $args=[])
 * @method static string format_size() format_size(number $size)
 * @method static string get_accessible_file() get_accessible_file(string $path, $suffix=null)
 * @method static Document get_document() get_document()
 * @method static string normalize() normalize(string $str)
 * @method static string render_exception() render_exception(\Exception $exception)
 * @method static string t() t(string $str, array $args=[], array $options=[])
 */
class Helpers
{
	static private $mapping = [

		'format'               => [ __CLASS__, 'default_format' ],
		'format_size'          => [ __CLASS__, 'default_format_size' ],
		'get_accessible_file'  => [ __CLASS__, 'default_get_accessible_file' ],
		'get_document'         => [ __CLASS__, 'default_get_document' ],
		'normalize'            => [ __CLASS__, 'default_normalize' ],
		'render_exception'     => [ __CLASS__, 'default_render_exception' ],
		't'                    => [ __CLASS__, 'default_t' ]

	];

	/**
	 * Calls the callback of a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param array $arguments Arguments.
	 *
	 * @return mixed
	 */
	static public function __callStatic($name, array $arguments)
	{
		return call_user_func_array(self::$mapping[$name], $arguments);
	}

	/**
	 * Patches a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param callable $callback Callback.
	 *
	 * @throws \RuntimeException is attempt to patch an undefined function.
	 */
	static public function patch($name, $callback)
	{
		if (empty(self::$mapping[$name]))
		{
			throw new \RuntimeException("Undefined patchable: $name.");
		}

		self::$mapping[$name] = $callback;
	}

	/*
	 * fallbacks
	 */

	/**
	 * This method is the fallback for the {@link format()} function.
	 *
	 * @param string $str
	 * @param array $args
	 *
	 * @return string
	 *
	 * @see \Brickrouge\format()
	 */
	static protected function default_format($str, array $args = [])
	{
		return \ICanBoogie\format($str, $args);
	}

	/**
	 * This method is the fallback for the {@link format_size()} function.
	 *
	 * @param int $size
	 *
	 * @return string
	 *
	 * @see \Brickrouge\format_size()
	 */
	static protected function default_format_size($size)
	{
		if ($size < 1024)
		{
			$str = ":size\xC2\xA0b";
		}
		else if ($size < 1024 * 1024)
		{
			$str = ":size\xC2\xA0Kb";
			$size = $size / 1024;
		}
		else if ($size < 1024 * 1024 * 1024)
		{
			$str = ":size\xC2\xA0Mb";
			$size = $size / (1024 * 1024);
		}
		else
		{
			$str = ":size\xC2\xA0Gb";
			$size = $size / (1024 * 1024 * 1024);
		}

		return t($str, [ ':size' => round($size) ]);
	}

	/**
	 * This method is the fallback for the {@link normalize()} function.
	 *
	 * @param string $str
	 * @param string $separator
	 * @param string $charset
	 *
	 * @return string
	 *
	 * @see \Brickrouge\normalize()
	 */
	static protected function default_normalize($str, $separator = '-', $charset = CHARSET)
	{
		$str = str_replace('\'', '', $str);

		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
		$str = preg_replace('#&[^;]+;#', '', $str);

		$str = strtolower($str);
		$str = preg_replace('#[^a-z0-9]+#', $separator, $str);
		$str = trim($str, $separator);

		return $str;
	}

	/**
	 * This method is the fallback for the {@link t()} function.
	 *
	 * We usually rely on the ICanBoogie framework I18n features to translate our string, if it is
	 * not available we simply format the string using the {@link Brickrouge\format()} function.
	 *
	 * @param string $str
	 * @param array $args
	 *
	 * @return string
	 *
	 * @see \Brickrouge\t()
	 */
	static protected function default_t($str, array $args = [])
	{
		return format($str, $args);
	}

	/**
	 * This method is the fallback for the {@link get_document()} function.
	 *
	 * @see \Brickrouge\get_document()
	 */
	static protected function default_get_document()
	{
		if (self::$document === null)
		{
			self::$document = new Document();
		}

		return self::$document;
	}

	private static $document;

	/**
	 * This method is the fallback for the {@link render_exception()} function.
	 *
	 * @param \Exception $exception
	 *
	 * @return string
	 *
	 * @see \Brickrouge\render_exception()
	 */
	static protected function default_render_exception(\Exception $exception)
	{
		return (string) $exception;
	}

	/**
	 * This method is the fallback for the {@link get_accessible_file()} function.
	 *
	 * @param string $path Absolute path to the web inaccessible file.
	 *
	 * @return string The pathname of the replacement.
	 *
	 * @throws \Exception if the replacement file could not be created.
	 *
	 * @see \Brickrouge\get_accessible_file()
	 */
	static protected function default_get_accessible_file($path)
	{
		static $publish_assets;

		if (!$publish_assets)
		{
			$publish_assets = new PublishAssets(ACCESSIBLE_ASSETS);
		}

		return $publish_assets($path);
	}
}
