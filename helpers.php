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