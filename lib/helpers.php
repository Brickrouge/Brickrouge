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
 * Inserts a value in a array before, or after, at given key.
 *
 * Numeric keys are not preserved.
 *
 * @param $array
 * @param $relative
 * @param $value
 * @param $key
 * @param $after
 *
 * @return A new array with the value inserted at the requested position.
 */
function array_insert($array, $relative, $value, $key=null, $after=false)
{
	$keys = array_keys($array);
	$pos = array_search($relative, $keys, true);

	if ($after)
	{
		$pos++;
	}

	$spliced = array_splice($array, $pos);

	if ($key !== null)
	{
		$array = array_merge($array, array($key => $value));
	}
	else
	{
		array_unshift($spliced, $value);
	}

	return array_merge($array, $spliced);
}

function array_flatten(array $array)
{
	$result = $array;

	foreach ($array as $key => &$value)
	{
		_array_flatten_callback($result, '', $key, $value);
	}

	return $result;
}

function _array_flatten_callback(&$result, $pre, $key, &$value)
{
	if (is_array($value))
	{
		foreach ($value as $vk => &$vv)
		{
			_array_flatten_callback($result, $pre ? ($pre . '[' . $key . ']') : $key, $vk, $vv);
		}
	}
	else if (is_object($value))
	{
		// FIXME: throw new Exception('Don\'t know what to do with objects: \1', $value);
	}
	else
	{
		/* FIXME-20100520: this has been disabled because sometime empty values (e.g. '') are
		 * correct values. The function was first used with Brickrouge\Form which made sense at the time
		 * but now changing values would be a rather strange behaviour.
		#
		# a trick to create undefined values
		#

		if (!strlen($value))
		{
			$value = null;
		}
		*/

		if ($pre)
		{
			#
			# only arrays are flattened
			#

			$pre .= '[' . $key . ']';

			$result[$pre] = $value;
		}
		else
		{
			#
			# simple values are kept intact
			#

			$result[$key] = $value;
		}
	}
}

/**
 * Sorts an array using a stable sorting algorithm while preserving its keys.
 *
 * A stable sorting algorithm maintains the relative order of values with equal keys.
 *
 * The array is always sorted in ascending order but one can use the {@link array_reverse()}
 * function to reverse the array. Also keys are preserved, even numeric ones, use the
 * {@link array_values()} function to create an array with an ascending index.
 *
 * @param array $array
 * @param callable $picker
 */
function stable_sort(&$array, $picker=null)
{
	static $transform, $restore;

	$i = 0;

	if (!$transform)
	{
		$transform = function(&$v, $k) use (&$i)
		{
			$v = array($v, ++$i, $k, $v);
		};

		$restore = function(&$v, $k)
		{
			$v = $v[3];
		};
	}

	if ($picker)
	{
		array_walk
		(
			$array, function(&$v, $k) use (&$i, $picker)
			{
				$v = array($picker($v), ++$i, $k, $v);
			}
		);
	}
	else
	{
		array_walk($array, $transform);
	}

	asort($array);

	array_walk($array, $restore);
}

/**
 * Convert special characters to HTML entities.
 *
 * @param string $str The string being converted.
 * @param string $charset Defines character set used in conversion. The default charset is
 * {@link BrickRoute\CHARSET} (utf-8).
 *
 * @return string
 */
function escape($str, $charset=CHARSET)
{
	return htmlspecialchars($str, ENT_COMPAT, $charset);
}

function escape_all($str, $charset=CHARSET)
{
	return htmlentities($str, ENT_COMPAT, $charset);
}

/**
 * Shortens a string at a specified position.
 *
 * @param string $str The string to shorten.
 * @param int $length The desired length of the string.
 * @param float $position Position at which characters can be removed.
 * @param bool $shortened `true` if the string was shortened, `false` otherwise.
 *
 * @return string
 */
function shorten($str, $length=32, $position=.75, &$shortened=null)
{
	$l = mb_strlen($str);

	if ($l <= $length)
	{
		return $str;
	}

	$length--;
	$position = (int) ($position * $length);

	if ($position == 0)
	{
		$str = '…' . mb_substr($str, $l - $length);
	}
	else if ($position == $length)
	{
		$str = mb_substr($str, 0, $length) . '…';
	}
	else
	{
		$str = mb_substr($str, 0, $position) . '…' . mb_substr($str, $l - ($length - $position));
	}

	$shortened = true;

	return $str;
}

function dump($value)
{
	if (function_exists('xdebug_var_dump'))
	{
		ob_start();

		xdebug_var_dump($value);

		$value = ob_get_clean();
	}
	else
	{
		$value = '<pre>' . escape(print_r($value, true)) . '</pre>';
	}

	return $value;
}

/**
 * Returns a web accessible path to a web inaccessible file.
 *
 * If the accessible file does not exists it is created.
 *
 * The directory where the files are copied is defined by the {@link ACCESSIBLE_ASSETS} constant.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::get_accessible_file()}.
 *
 * @param string $path Absolute path to the web inaccessible file.
 * @param string $suffix Optional suffix for the web accessible filename.
 *
 * @return string The pathname of the replacement.
 *
 * @throws \Exception if the replacement file could not be created.
 */
function get_accessible_file($path, $suffix=null)
{
	return Helpers::get_accessible_file($path, $suffix);
}

/**
 * Formats the given string by replacing placeholders with the given values.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::format()}.
 *
 * @param string $str The string to format.
 * @param array $args An array of replacement for the placeholders. Occurrences in `$str` of any
 * key in `$args` are replaced with the corresponding sanitized value. The sanitization function
 * depends on the first character of the key:
 *
 * - `:key`: Replace as is. Use this for text that has already been sanitized.
 * - `!key`: Sanitize using the {@link escape()} function.
 * - `%key`: Sanitize using the {@link escape()} function and wrap inside an `EM` markup.
 *
 * Numeric indexes can also be used e.g `\2` or `{2}` are replaced by the value of the index
 * 2.
 *
 * @return string
 */
function format($str, array $args=array())
{
	return Helpers::format($str, $args);
}

/**
 * Formats a number into a size with unit (o, Ko, Mo, Go).
 *
 * Before the string is formatted it is localised with the {@link t()} function.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::format_size()}.
 *
 * @param int $size
 *
 * @return string
 */
function format_size($size)
{
	return Helpers::format_size($size);
}

/**
 * Normalizes the input provided and returns the normalized string.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::normalize()}.
 *
 * @param string $str The string to normalize.
 * @param string $separator Whitespaces replacement.
 * @param string $charset The charset of the input string, defaults to {@link Brickrouge\CHARSET}
 * (utf-8).
 *
 * @return string
 */
function normalize($str, $separator='-', $charset=CHARSET)
{
	return Helpers::normalize($str, $separator, $charset);
}

/**
 * Translates a string to the current language or to a given language.
 *
 * The native string language is supposed to be english (en).
 *
 * Note: Calls to this function are forwarded to {@link Helpers::t}.
 *
 * @param string $str The native string to translate.
 * @param array $args An array of replacements to make after the translation. The replacement is
 * handled by the {@link format()} function.
 * @param array $options An array of additional options, with the following elements:
 * - 'default': The default string to use if the translation failed.
 * - 'scope': The scope of the translation.
 *
 * @return mixed
 *
 * @see ICanBoogie\I18n\Translator
 */
function t($str, array $args=array(), array $options=array())
{
	return Helpers::t($str, $args, $options);
}

/**
 * Returns the global document object.
 *
 * This document is used by classes when they need to add assets. Once assets are collected one can
 * simple echo the assets while building the response HTML.
 *
 * Example:
 *
 * <?php
 *
 * namespace Brickrouge;
 *
 * $document = get_document();
 * $document->css->add(Brickrouge\ASSETS . 'brickrouge.css');
 * $document->js->add(Brickrouge\ASSETS . 'brickrouge.js');
 *
 * ?><!DOCTYPE html>
 * <html>
 * <head>
 * <?php echo $document->css ?>
 * </head>
 * <body>
 * <?php echo $document->js ?>
 * </body>
 * </html>
 *
 * Note: Calls to this function are forwarded to {@link Helpers::get_document()}.
 *
 * @return Document
 */
function get_document()
{
	return Helpers::get_document();
}

/**
 * Checks if the session is started, and start it otherwise.
 *
 * The session is used by the {@link Form} class to store validation errors and store
 * its forms for later validation. Take a look at the {@link Form::validate()} and
 * {@link Form::save()} methods.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::check_session()}.
 */
function check_session()
{
	Helpers::check_session();
}

/**
 * Stores of form for later validation.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::store_form()}.
 *
 * @param Form $form The form to store.
 *
 * @return string A key that must be used to retrieve the form.
 */
function store_form(Form $form)
{
	return Helpers::store_form($form);
}

/**
 * Retrieve a stored form.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::retrieve_form()}.
 *
 * @param string $key Key of the form to retrieve.
 *
 * @return Form|null The retrieved form or null if none where found for the specified key.
 */
function retrieve_form($key)
{
	return Helpers::retrieve_form($key);
}

/**
 * Stores the validation errors of a form.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::store_form_errors()}.
 *
 * @param string $name The name of the form.
 * @param array $errors The validation errors of the form.
 */
function store_form_errors($name, $errors)
{
	Helpers::store_form_errors($name, $errors);
}

/**
 * Retrieves the validation errors of a form.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::retrieve_form_errors()}.
 *
 * @param string $name The name if the form.
 *
 * @return array
 */
function retrieve_form_errors($name)
{
	return Helpers::retrieve_form_errors($name);
}

/**
 * Renders an exception into a string.
 *
 * Note: Calls to this function are forwarded to {@link Helpers::render_exception()}.
 *
 * @param \Exception $exception
 *
 * @return string
 */
function render_exception(\Exception $exception)
{
	return Helpers::render_exception($exception);
}

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
 * @method string format() format(string $str, array $args=array())
 * @method string format_size() format_size(number $size)
 * @method string get_accessible_file() get_accessible_file(string $path, $suffix=null)
 * @method Document get_document() get_document()
 * @method string normalize() normalize(string $str)
 * @method string render_exception() render_exception(\Exception $exception)
 * @method Form retrieve_form() retrieve_form(string $name)
 * @method \ICanboogie\Errors retrieve_form_errors() retrieve_form_errors(string $name)
 * @method string store_form() store_form(Form $form)
 * @method void store_form_errors() store_form_errors(string $name, \ICanBoogie\Errors $errors)
 * @method string t() t(string $str, array $args=array(), array $options=array())
 */
class Helpers
{
	static private $jumptable = array
	(
		'check_session' => array(__CLASS__, 'check_session'),
		'format' => array(__CLASS__, 'format'),
		'format_size' => array(__CLASS__, 'format_size'),
		'get_accessible_file' => array(__CLASS__, 'get_accessible_file'),
		'get_document' => array(__CLASS__, 'get_document'),
		'normalize' => array(__CLASS__, 'normalize'),
		'render_exception' => array(__CLASS__, 'render_exception'),
		'retrieve_form' => array(__CLASS__, 'retrieve_form'),
		'retrieve_form_errors' => array(__CLASS__, 'retrieve_form_errors'),
		'store_form' => array(__CLASS__, 'store_form'),
		'store_form_errors' => array(__CLASS__, 'store_form_errors'),
		't' => array(__CLASS__, 't')
	);

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
	 * @param collable $callback Callback.
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
	static private function format($str, array $args=array())
	{
		if (!$args)
		{
			return $str;
		}

		$holders = array();
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

		return t($str, array(':size' => round($size)));
	}

	/**
	 * This method is the fallback for the {@link normalize()} function.
	 */
	static private function normalize($str, $separator='-', $charset=CHARSET)
	{
		$str = str_replace('\'', '', $str);

		$str = htmlentities($str, ENT_NOQUOTES, $charset);
		$str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
		$str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str); // pour les ligatures e.g. '&oelig;'
		$str = preg_replace('#&[^;]+;#', '', $str); // supprime les autres caractères

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
	static private function t($str, array $args=array(), array $options=array())
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
			return;
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
		return isset(self::$errors[$name]) ? self::$errors[$name] : array();
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
	static private function get_accessible_file($path, $suffix=null)
	{
		$key = sprintf('%s-%04x%s.%s', md5($path), strlen($path), ($suffix ? '-' . $suffix : ''), pathinfo($path, PATHINFO_EXTENSION));
		$replacement_path = ACCESSIBLE_ASSETS;
		$replacement = $replacement_path . $key;

		if (!is_writable($replacement_path))
		{
			throw new \Exception(format('Unable to make the file %path web accessible, the destination directory %replacement_path is not writtable.', array('path' => $path, 'replacement_path' => $replacement_path)));
		}

		if (!file_exists($replacement) || filemtime($path) > filemtime($replacement))
		{
			file_put_contents($replacement, file_get_contents($path));
		}

		return $replacement;
	}
}