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

/**
 * Brickrouge helpers.
 *
 * The following helpers are patchable:
 *
 * - {@link check_session()}
 * - {@link format()}
 * - {@link format_size()}
 * - {@link get_accessible_file()}
 * - {@link get_document()}
 * - {@link normalize()}
 * - {@link render_exception()}
 * - {@link retrieve_form()}
 * - {@link retrieve_form_errors()}
 * - {@link store_form()}
 * - {@link store_form_errors()}
 * - {@link t()}
 *
 * @method void check_session() check_session()
 * @method string format() format(string $str, array $args=[])
 * @method string format_size() format_size(number $size)
 * @method string get_accessible_file() get_accessible_file(string $path, $suffix=null)
 * @method Document get_document() get_document()
 * @method string normalize() normalize(string $str)
 * @method string render_exception() render_exception(\Exception $exception)
 * @method Form retrieve_form() retrieve_form(string $name)
 * @method Errors retrieve_form_errors() retrieve_form_errors(string $name)
 * @method string store_form() store_form(Form $form)
 * @method void store_form_errors() store_form_errors(string $name, Errors $errors)
 * @method string t() t(string $str, array $args=[], array $options=[])
 */
class Helpers
{
	static private $jumptable = [

		'check_session'        => [ __CLASS__, 'check_session' ],
		'format'               => [ __CLASS__, 'format' ],
		'format_size'          => [ __CLASS__, 'format_size' ],
		'get_accessible_file'  => [ __CLASS__, 'get_accessible_file' ],
		'get_document'         => [ __CLASS__, 'get_document' ],
		'normalize'            => [ __CLASS__, 'normalize' ],
		'render_exception'     => [ __CLASS__, 'render_exception' ],
		'retrieve_form'        => [ __CLASS__, 'retrieve_form' ],
		'retrieve_form_errors' => [ __CLASS__, 'retrieve_form_errors' ],
		'store_form'           => [ __CLASS__, 'store_form' ],
		'store_form_errors'    => [ __CLASS__, 'store_form_errors' ],
		't'                    => [ __CLASS__, 't' ]

	];

	/**
	 * Calls the callback of a patchable function.
	 *
	 * @param string $name Name of the function.
	 * @param array $arguments Arguments.
	 *
	 * @return mixed
	 */
	static public function __callstatic($name, array $arguments)
	{
		return call_user_func_array(self::$jumptable[$name], $arguments);
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
		if (empty(self::$jumptable[$name]))
		{
			throw new \RuntimeException("Undefined patchable: $name.");
		}

		self::$jumptable[$name] = $callback;
	}

	/*
	 * fallbacks
	 */

	/**
	 * This method is the fallback for the {@link format()} function.
	 */
	static private function format($str, array $args = [])
	{
		if (!$args)
		{
			return $str;
		}

		$holders = [];
		$i = 0;

		foreach ($args as $key => $value)
		{
			++$i;

			if (is_array($value) || is_object($value))
			{
				$value = dump($value);
			}
			else if (is_bool($value))
			{
				$value = $value ? '<em>true</em>' : '<em>false</em>';
			}
			else if (is_null($value))
			{
				$value = '<em>null</em>';
			}

			if (is_string($key))
			{
				switch ($key{0})
				{
					case ':': break;
					case '!': $value = escape($value); break;
					case '%': $value = '<q>' . escape($value) . '</q>'; break;

					default:
					{
						$escaped_value = escape($value);

						$holders['!' . $key] = $escaped_value;
						$holders['%' . $key] = '<q>' . $escaped_value . '</q>';

						$key = ':' . $key;
					}
				}
			}
			else if (is_numeric($key))
			{
				$key = '\\' . $i;
				$holders['{' . $i . '}'] = $value;
			}

			$holders[$key] = $value;
		}

		return strtr($str, $holders);
	}

	/**
	 * This method is the fallback for the {@link format_size()} function.
	 */
	static private function format_size($size)
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
	 */
	static private function normalize($str, $separator = '-', $charset = CHARSET)
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
	 */
	static private function t($str, array $args = [], array $options = [])
	{
		return format($str, $args);
	}

	/**
	 * This method is the fallback for the {@link get_document()} function.
	 */
	static private function get_document()
	{
		if (self::$document === null)
		{
			self::$document = new Document();
		}

		return self::$document;
	}

	private static $document;

	/**
	 * This method is the fallback for the {@link check_session()} function.
	 */
	static private function check_session()
	{
		if (session_id())
		{
			return;
		}

		session_start();
	}

	const STORE_KEY = 'brickrouge.stored_forms';
	const STORE_MAX = 10;

	/**
	 * Fallback for the {@link store_form()} function.
	 *
	 * The form is saved in the session in the STORE_KEY array.
	 *
	 * @param Form $form
	 *
	 * @return string The key to use to retrieve the form.
	 */
	static private function store_form(Form $form)
	{
		check_session();

		#
		# before we store anything, we do some cleanup. in order to avoid sessions filled with
		# used forms. We only maintain a few. The limit is set using the STORE_MAX constant.
		#

		if (isset($_SESSION[self::STORE_KEY]))
		{
			$n = count($_SESSION[self::STORE_KEY]);

			if ($n > self::STORE_MAX)
			{
				$_SESSION[self::STORE_KEY] = array_slice($_SESSION[self::STORE_KEY], $n - self::STORE_MAX);
			}
		}

		$key = md5(uniqid(mt_rand(), true));

		$_SESSION[self::STORE_KEY][$key] = serialize($form);

		return $key;
	}

	/**
	 * Fallback for the {@link retrieve_form()} function.
	 *
	 * @param string $key
	 *
	 * @return void|Form The retrieved form or null if the key matched none.
	 */
	static private function retrieve_form($key)
	{
		check_session();

		if (empty($_SESSION[self::STORE_KEY][$key]))
		{
			return null;
		}

		$form = unserialize($_SESSION[self::STORE_KEY][$key]);

		unset($_SESSION[self::STORE_KEY][$key]);

		return $form;
	}

	static private $errors;

	/**
	 * This method is the fallback for the {@link store_form_errors()} function.
	 */
	static private function store_form_errors($name, $errors)
	{
		self::$errors[$name] = $errors;
	}

	/**
	 * This method is the fallback for the {@link retrieve_form_errors()} function.
	 */
	static private function retrieve_form_errors($name)
	{
		return isset(self::$errors[$name]) ? self::$errors[$name] : [];
	}

	/**
	 * This method is the fallback for the {@link render_exception()} function.
	 *
	 * @param \Exception $exception
	 *
	 * @return string
	 */
	static private function render_exception(\Exception $exception)
	{
		return (string) $exception;
	}

	/**
	 * This method is the fallback for the {@link get_accessible_file()} function.
	 *
	 * @param string $path Absolute path to the web inaccessible file.
	 * @param string $suffix Optional suffix for the web accessible filename.
	 *
	 * @return string The pathname of the replacement.
	 *
	 * @throws \Exception if the replacement file could not be created.
	 */
	static private function get_accessible_file($path, $suffix = null)
	{
		$key = ($suffix ? '-' . $suffix : '') . self::hash_file($path) . '.' . pathinfo($path, PATHINFO_EXTENSION);
		$replacement_path = ACCESSIBLE_ASSETS;
		$replacement = $replacement_path . $key;

		if (!is_writable($replacement_path))
		{
			throw new \Exception(format('Unable to make the file %path web accessible, the destination directory %replacement_path is not writtable.', [ 'path' => $path, 'replacement_path' => $replacement_path ]));
		}

		if (!file_exists($replacement))
		{
			file_put_contents($replacement, file_get_contents($path));
		}

		return $replacement;
	}

	/**
	 * Hash a file using SHA-384 and returns a base64url string.
	 *
	 * @param string $pathname Absolute pathname of the file to hash.
	 *
	 * @return string A base64url string.
	 */
	static private function hash_file($pathname)
	{
		return strtr(base64_encode(hash_file('sha384', $pathname, true)), [

			'+' => '-',
			'/' => '_'

		]);
	}
}
