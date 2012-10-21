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

use ICanBoogie\FileCache;

/**
 * An HTML document.
 *
 * @property $assets array The Javascript and CSS assets used by the document.
 */
class Document extends \ICanBoogie\Object
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
		global $core;

		$use_cache = !empty($core->config['cache assets']);

		$this->body = new Element('body');
		$this->js = new JSCollector($use_cache);
		$this->css = new CSSCollector($use_cache);
	}

	/**
	 * Returns the Javascript and CSS assets used by the document as an array or URLs.
	 *
	 * @return array The assets used by the document.
	 */
	protected function volatile_get_assets()
	{
		return array
		(
			'css' => $this->css->get(),
			'js' => $this->js->get()
		);
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
	protected function volatile_set_assets(array $assets)
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

		foreach ($stack as $trace)
		{
			if (empty($trace['file']) || $trace['file'] == __FILE__)
			{
				continue;
			}

			return dirname($trace['file']);
		}
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
				trigger_error(format('Phar file %path does not exists.', array('%path' => $path)));

				return;
			}
		}

		$root = DOCUMENT_ROOT;

		# trying from directory

		$tried = array($path);
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
			$tries[] = $root . $path;
			$realpath = realpath($root . $path);
		}

		#
		# We can't find a matching file :-(
		#

		if (!$realpath)
		{
			trigger_error(format('Unable to resolve path %path to an URL, tried: !tried', array('path' => $path, 'tried' => implode(', ', $tried))));

			return;
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

/**
 * Root class for documents assets collectors.
 */
abstract class AssetsCollector
{
	/**
	 * Collected assets
	 *
	 * @var array
	 */
	protected $collected = array();

	/**
	 * Whether the collected assets should be cached.
	 *
	 * @var bool
	 */
	public $use_cache = false;

	/**
	 * Sets the cache policy according to the configuration.
	 */
	public function __construct($use_cache=false)
	{
		$this->use_cache = $use_cache;
	}

	/**
	 * Adds an asset to the collection.
	 *
	 * @param string $path Path, or relative path to the asset.
	 * @param int $weight Weight of the asset in the collection.
	 * @param string|null $root Root used to resolve the asset path into a URL.
	 *
	 * @return AssetsCollector Return the object itself for chainable calls.
	 */
	public function add($path, $weight=0, $root=null)
	{
		$url = Document::resolve_url($path, $root);

		$this->collected[$url] = $weight;

		return $this;
	}

	/**
	 * Returns the collected assets as an array of URL.
	 *
	 * @return array
	 */
	public function get()
	{
		$by_priority = array();

		foreach ($this->collected as $url => $priority)
		{
			$by_priority[$priority][] = $url;
		}

		ksort($by_priority);

		$sorted = array();

		foreach ($by_priority as $urls)
		{
			$sorted = array_merge($sorted, $urls);
		}

		return $sorted;
	}

	/**
	 * Clears the collected assets.
	 */
	public function clear()
	{
		$this->collected = array();
	}

	abstract public function cache_construct(FileCache $cache, $key, array $userdata);
}

/**
 * Collector for CSS assets.
 */
class CSSCollector extends AssetsCollector
{
	public function __toString()
	{
		global $core;

		$collected = $this->get();

		try
		{
			if ($this->use_cache)
			{
				$recent = 0;
				$root = DOCUMENT_ROOT;

				foreach ($collected as $file)
				{
					$recent = max($recent, filemtime($root . $file));
				}

				$cache = new FileCache
				(
					array
					(
						FileCache::T_REPOSITORY => $core->config['repository.files'] . '/assets',
						FileCache::T_MODIFIED_TIME => $recent
					)
				);

				$key = sha1(implode(',', $collected)) . '.css';

				$rc = $cache->get($key, array($this, 'cache_construct'), array($collected));

				if ($rc)
				{
					$list = json_encode($collected);

					return <<<EOT

<link type="text/css" href="{$cache->repository}/{$key}" rel="stylesheet" />

<script type="text/javascript">

var brickrouge_cached_css_assets = $list;

</script>

EOT;

				}
			}
		}
		catch (\Exception $e) { echo render_exception($e); }

		#
		# default ouput
		#

		$rc = '';

		foreach ($collected as $url)
		{
			$rc .= '<link type="text/css" href="' . escape($url) . '" rel="stylesheet" />' . PHP_EOL;
		}

		return $rc;
	}

	public function cache_construct(FileCache $cache, $key, array $userdata)
	{
		list($collected) = $userdata;

		$rc = '/* Compiled CSS file generated by ' . __CLASS__ . ' */' . PHP_EOL . PHP_EOL;

		foreach ($collected as $url)
		{
			$contents = file_get_contents(DOCUMENT_ROOT . $url);
			$contents = preg_replace('/url\(([^\)]+)/', 'url(' . dirname($url) . '/$1', $contents);

			$rc .= $contents . PHP_EOL;
		}

		file_put_contents(getcwd() . '/' . $key, $rc);

		return $key;
	}
}

/**
 * Collector for Javascript assets.
 */
class JSCollector extends AssetsCollector
{
	public function __toString()
	{
		global $core;

		$collected = $this->get();

		#
		# exchange with minified versions
		#

		if (0)
		{
			$root = DOCUMENT_ROOT;
			$repository = $core->config['repository.files'] . '/assets/minified/';

			foreach ($collected as $file)
			{
				$minified_key = md5($file);

				if (!file_exists($root . $repository . $minified_key))
				{
					echo "<code>create minified ($minified_key) for $file</code><br />";

					$cmd = "java -jar /users/serveurweb/Sites/yuicompressor-2.4.6.jar {$root}{$file} -o {$root}{$repository}{$minified_key}.js --charset utf-8";

					echo "<code><strong>cmd:</strong> $cmd</code>";

					$output = null;
					$return_var = null;

					exec($cmd, $output, $return_var);

					var_dump($output, $return_var);
				}
			}
		}

		#
		# cached ouput
		#

		try
		{
			if ($this->use_cache)
			{
				$recent = 0;
				$root = DOCUMENT_ROOT;

				foreach ($collected as $file)
				{
					$recent = max($recent, filemtime($root . $file));
				}

				$cache = new FileCache
				(
					array
					(
						FileCache::T_REPOSITORY => $core->config['repository.files'] . '/assets',
						FileCache::T_MODIFIED_TIME => $recent
					)
				);

				$key = sha1(implode(',', $collected)) . '.js';

				$rc = $cache->get($key, array($this, 'cache_construct'), array($collected));

				if ($rc)
				{
					return PHP_EOL . PHP_EOL . '<script type="text/javascript" src="' . $cache->repository . '/' . $key . '"></script>' . PHP_EOL . PHP_EOL;
				}
			}
		}
		catch (\Exception $e) { echo render_exception($e); }

		#
		# default output
		#

		$rc = '';

		foreach ($collected as $url)
		{
			$rc .= '<script type="text/javascript" src="' . escape($url) . '"></script>' . PHP_EOL;
		}

		return $rc;
	}

	public function cache_construct(FileCache $cache, $key, array $userdata)
	{
		list($collected) = $userdata;

		$class = __CLASS__;
		$date = date('Y-m-d');
		$list = json_encode($collected);

		$content = <<<EOT
/*
 * Compiled Javascript file generated by $class ($date)
 */

var brickrouge_cached_js_assets = $list;

EOT;

		foreach ($collected as $url)
		{
			$content .= file_get_contents(DOCUMENT_ROOT . $url) . PHP_EOL;
		}

		file_put_contents(getcwd() . '/' . $key, $content);

		return $key;
	}
}