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

/**
 * Convert special characters to HTML entities.
 *
 * @param string $str The string being converted.
 * @param string $charset Defines character set used in conversion. The default character set is
 * BrickRoute\CHARSET (utf-8).
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
 * @see Patchable::fallback_format
 */
function format($str, array $args=array())
{
	return call_user_func(Patchable::$callback_format, $str, $args);
}

/**
 * @see Patchable::fallback_format_size
 */
function format_size($size)
{
	return call_user_func(Patchable::$callback_format_size, $size);
}

/**
 * @see Patchable::fallback_normalize
 */
function normalize($str, $separator='-', $charset=CHARSET)
{
	return call_user_func(Patchable::$callback_normalize, $str, $separator, $charset);
}

/**
 * @see Patchable::fallback_translate
 */
function t($str, array $args=array(), array $options=array())
{
	return call_user_func(Patchable::$callback_translate, $str, $args, $options);
}

class Patchable
{
	static $callback_format = array(__CLASS__, 'fallback_format');
	static $callback_format_size = array(__CLASS__, 'fallback_format_size');
	static $callback_normalize = array(__CLASS__, 'fallback_normalize');
	static $callback_translate = array(__CLASS__, 'fallback_translate');

	/**
	 * Formats the given string by replacing placeholders with the given values.
	 *
	 * This function is a copy of the format function provided by the ICanBoogie framework and is
	 * the fallback for the format() function.
	 *
	 * @param string $str The string to format.
	 * @param array $args An array of replacement for the plaecholders. Occurences in $str of any
	 * key in $args are replaced with the corresponding sanitized value. The sanitization function
	 * depends on the first character of the key:
	 *
	 * * :key: Replace as is. Use this for text that has already been sanitized.
	 * * !key: Sanitize using the `escape()` function.
	 * * %key: Sanitize using the `escape()` function and wrap inside a "EM" markup.
	 *
	 * Numeric indexes can also be used e.g '\2' or "{2}" are replaced by the value of the index
	 * "2".
	 *
	 * @return string
	 */
	public static function fallback_format($str, array $args=array())
	{
		if (!$args)
		{
			return $str;
		}

		$holders = array();

		$i = 0;

		foreach ($args as $key => $value)
		{
			$i++;

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
			else if (is_string($key))
			{
				switch ($key{0})
				{
					case ':': break;
					case '!': $value = escape($value); break;
					case '%': $value = '<q>' . escape($value) . '</q>'; break;

					default:
					{
						$escaped_value = escape($value);

						$holders["!$key"] = $escaped_value;
						$holders["%$key"] = '<q>' . $escaped_value . '</q>';

						$key = ":$key";
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

	static public function fallback_format_size($size)
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

	static public function fallback_normalize($str, $separator='-', $charset=CHARSET)
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
	 * Fallback for the translating function "t".
	 *
	 * @param string $str
	 * @param array $args
	 * @param array $options
	 *
	 * @return string
	 */
	static public function fallback_translate($str, array $args=array(), array $options=array())
	{
		return format($str, $args);
	}
}