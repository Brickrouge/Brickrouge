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

use ICanBoogie\Prototyped;

/**
 * An HTML document.
 *
 * @property $assets array The Javascript and CSS assets used by the document.
 */
class Document extends Prototyped
{
	public $body;

	/**
	 * @var JSCollector Collector for Javascript assets.
	 */
	public $js;

	/**
	 * @var CSSCollector Collector for CSS assets.
	 */
	public $css;

	/**
	 * Constructor.
	 *
	 * Creates the Javascript and CSS collectors.
	 */
	public function __construct()
	{
		$use_cache = false;

		if (function_exists('ICanBoogie\app'))
		{
			$use_cache = !empty(\ICanBoogie\app()->config['cache assets']);
		}

		$this->body = new Element('body');
		$this->js = new JSCollector($use_cache);
		$this->css = new CSSCollector($use_cache);
	}

	/**
	 * Returns the Javascript and CSS assets used by the document as an array or URLs.
	 *
	 * @return array The assets used by the document.
	 */
	protected function get_assets()
	{
		return [

			'css' => $this->css->get(),
			'js' => $this->js->get()

		];
	}

	/**
	 * Sets the assets of the document.
	 *
	 * @param array $assets An array where CSS and JS assets are stored under the 'css' and 'js'
	 * keys respectively. Each asset is defined as a key/value pair where the key if the path to
	 * the asset and the key is its priority.
	 *
	 * @example
	 *
	 * $document->assets = array
	 * (
	 *     'css' => array('brickrouge.css' => 0),
	 *     'js' => array('brickrouge.js' => 0)
	 * );
	 */
	protected function set_assets(array $assets)
	{
		unset($this->assets);
		$this->add_assets($assets);
	}

	/**
	 * Clears JS and CSS assets.
	 *
	 * @example
	 *
	 * $document->js->add('brickrouge.js');
	 * $document->css->add('brickrouge.css');
	 *
	 * var_dump($document->assets);
	 * // ['css' => ['brickrouge.css'], 'js' => ['brickrouge.js']]
	 *
	 * unset($document->assets);
	 *
	 * var_dump($document->assets);
	 * // ['css' => [], 'js' => []]
	 */
	protected function __unset_assets()
	{
		$this->js->clear();
		$this->css->clear();
	}

	/**
	 * Adds a number of assets to the document.
	 *
	 * @param array $assets An array where CSS and JS assets are stored under the 'css' and 'js'
	 * keys respectively. Each asset is defined as a key/value pair where the key if the path to
	 * the asset and the key is its priority.
	 *
	 * @example
	 *
	 * $document->add_assets
	 * (
	 *     array
	 *     (
	 *         'css' => array('brickrouge.css' => 0),
	 *         'js' => array('brickrouge.js' => 0)
	 *     )
	 * );
	 */
	public function add_assets(array $assets)
	{
		if (!empty($assets['css']))
		{
			foreach ($assets['css'] as $path => $priority)
			{
				$this->css->add($path, $priority);
			}
		}

		if (!empty($assets['js']))
		{
			foreach ($assets['js'] as $path => $priority)
			{
				$this->js->add($path, $priority);
			}
		}
	}

	/**
	 * Tries to locate the file where the assets was added by searching for the first file which
	 * is not the file where our class is defined.
	 *
	 * @return string|null The path to the directory of the file or null if no file could be found.
	 */
	static private function resolve_root()
	{
		$stack = debug_backtrace();
		$excluded = [

			__FILE__,
			__DIR__ . DIRECTORY_SEPARATOR . 'AssetsCollector.php'
		];

		foreach ($stack as $trace)
		{
			if (empty($trace['file']) || in_array($trace['file'], $excluded))
			{
				continue;
			}

			return dirname($trace['file']);
		}

		return null;
	}

	/**
	 * Resolves a server path into a URL accessible from the `DOCUMENT_ROOT`.
	 *
	 * Unless the path uses a scheme (http://, https:// or phar://) it is always considered
	 * relative to the path specified by the $relative parameter or to the `DOCUMENT_ROOT`.
	 *
	 * @param string $path
	 * @param string $relative Relative path that can be used to resolve the path. If the
	 * parameter is null the method tries to _guess_ the relative path using the
	 * {@link resolve_root()} private method.
	 *
	 * @return string The URL resolved from the path.
	 */
	static public function resolve_url($path, $relative=null)
	{
		if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0)
		{
			return $path;
		}
		else if (strpos($path, 'phar://') === 0)
		{
			if (file_exists($path))
			{
				$path = get_accessible_file($path, 'phar');
			}
			else
			{
				trigger_error(format('Phar file %path does not exists.', [ '%path' => $path ]));

				return null;
			}
		}

		$root = DOCUMENT_ROOT;

		# trying from directory

		$tried = [ $path ];
		$realpath = realpath($path);

		# trying from relative

		if (!$realpath)
		{
			if (!$relative)
			{
				$relative = self::resolve_root() . DIRECTORY_SEPARATOR;
			}

			$tried[] = $relative . $path;
			$realpath = realpath($relative . $path);
		}

		# trying from document root

		if (!$realpath)
		{
			$tried[] = $root . $path;
			$realpath = realpath($root . $path);
		}

		#
		# We can't find a matching file :-(
		#

		if (!$realpath)
		{
			trigger_error(format('Unable to resolve path %path to an URL, tried: !tried', [ 'path' => $path, 'tried' => implode(', ', $tried) ]));

			return null;
		}

		#
		# If the file is not accessible from the document root, we create an accessible version.
		#

		if (strpos($realpath, $root) === false)
		{
			$realpath = get_accessible_file($realpath);
		}

		#
		# let's turn this pathname into a lovely URL
		#

		$url = substr($realpath, strlen($root));

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$url = strtr($url, '\\', '/');
		}

		if ($url{0} != '/')
		{
			$url = '/' . $url;
		}

		return $url;
	}
}
