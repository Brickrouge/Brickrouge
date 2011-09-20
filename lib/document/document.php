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

use ICanBoogie;
use ICanBoogie\Collector;
use ICanBoogie\Debug;
use ICanBoogie\FileCache;
use ICanBoogie\Object;

/**
 * @property $assets array Assets used by the document
 *
 * @todo: https://github.com/sstephenson/sprockets
 */
class Document extends Object
{
	static protected function resolve_root()
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
	 * Resolves a server path into a URL.
	 *
	 * @param string $path
	 * @param string $relative
	 *
	 * @return string The URL resolved from the path.
	 */
	static public function resolve_url($path, $relative=null)
	{
		if (strpos($path, 'http://') === 0)
		{
			return $path;
		}
		else if (strpos($path, 'phar://') === 0)
		{
			if (file_exists($path))
			{
				$key = sprintf('phar-%s-%04x.%s', md5($path), strlen($path), pathinfo($path, PATHINFO_EXTENSION));
				$replacement = ICanBoogie\DOCUMENT_ROOT . 'repository/files/assets/' . $key;

				if (!file_exists($replacement) || filemtime($path) > filemtime($replacement))
				{
					file_put_contents($replacement, file_get_contents($path));
				}

				$path = $replacement;
			}
		}

		$root = ICanBoogie\DOCUMENT_ROOT;

		#
		# Is the file relative the to the 'relative' path ?
		#
		# if the relative path is not defined, we obtain it from the backtrace stack
		#

		if (!$relative)
		{
			$relative = self::resolve_root();
		}

		$script_dir = dirname($_SERVER['SCRIPT_NAME']);

		/*
		 * TODO-20110616: file conflict !! if we want 'public/auto.js' relative to our file, and
		 * 'public/auto.js' exists at the root of the website, the second is used instead :-(
		 *
		 * Maybe only '/public/auto.js' should be checked against the website root.
		 */

		$tries = array();

		if ($path{0} == '/')
		{
			$tries[] = '';
		}

		$tries[] = $relative . DIRECTORY_SEPARATOR;

		if ($script_dir != '/')
		{
			$tries[] = $root . $script_dir;
		}

		$tries[] = $root . DIRECTORY_SEPARATOR;

		$url = null;
		$i = 0;

		foreach ($tries as &$try)
		{
			$i++;
			$try .= $path;

			if (!is_file($try))
			{
				continue;
			}

			$url = $try;

			break;
		}

		#
		# found nothing !
		#

		if (!$url)
		{
			Debug::trigger('Unable to resolve path %path to an URL, tried: :tried', array('%path' => $path, ':tried' => implode(', ', array_slice($tries, 0, $i))));

			return;
		}

		if (strpos($url, $root) === false)
		{
			$key = sprintf('unaccessible-%s-%04x.%s', md5($path), strlen($path), pathinfo($path, PATHINFO_EXTENSION));
			$replacement = ICanBoogie\DOCUMENT_ROOT . 'repository/files/assets/' . $key;

			if (!file_exists($replacement) || filemtime($path) > filemtime($replacement))
			{
				file_put_contents($replacement, file_get_contents($path));
			}

			$url = $replacement;
		}

		#
		# let's turn this ugly absolute path into a lovely URL
		#

		$url = realpath($url);

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$url = str_replace('\\', '/', $url);
		}

		$url = substr($url, strlen($root));

		if ($url{0} != '/')
		{
			$url = '/' . $url;
		}

		return $url;
	}

	/**
	 * Getter hook for the use ICanBoogie\Core::$document property.
	 *
	 * @return Document
	 */
	static public function hook_get_document()
	{
		global $document;

		return $document = new Document();
	}

	public $title;
	public $page_title;

	/**
	 * @var JSCollector Collector for Javascript assets.
	 */
	public $js;

	/**
	 * @var CSSCollector Collector for CSS assets.
	 */
	public $css;

	/**
	 * Returns the Javascript and CSS assets used by the document.
	 *
	 * @return array The assets used by the document.
	 */
	protected function __get_assets()
	{
		return $this->get_assets();
	}

	/**
	 * Creates the Javascript and CSS collectors.
	 */
	public function __construct()
	{
		$this->js = new Collector\JS();
		$this->css = new Collector\CSS();
	}

	protected function getHead()
	{
		$rc  = '<head>' . PHP_EOL;
		$rc .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . PHP_EOL;

		$rc .= '<title>' . $this->title . '</title>' . PHP_EOL;

		$rc .= $this->css;

		$rc .= '</head>' . PHP_EOL;

		return $rc;
	}

	protected function getBody()
	{
		return '<body></body>';
	}

	public function __toString()
	{
		global $core;

		try
		{
			$body = $this->getBody();
			$head = $this->getHead();

			$rc  = '<!DOCTYPE html>' . PHP_EOL;
			$rc .= '<html lang="' . $core->language . '">' . PHP_EOL;

			$rc .= $head;
			$rc .= $body;

			$rc .= '</html>';
		}
		catch (\Exception $e)
		{
			$rc = (string) $e;
		}

		return $rc;
	}

	/**
	 * Returns the Javascript and CSS assets as an array of URL.
	 *
	 * @return array
	 */
	public function get_assets()
	{
		return array
		(
			'css' => $this->css->get(),
			'js' => $this->js->get()
		);
	}

	/**
	 * Adds a number of assets to the document.
	 *
	 * @param array $assets
	 */
	public function add_assets(array $assets)
	{
		foreach ($assets['css'] as $path => $priority)
		{
			$this->css->add($path, $priority);
		}

		foreach ($assets['js'] as $path => $priority)
		{
			$this->js->add($path, $priority);
		}
	}
}

namespace ICanBoogie;

use BrickRouge\Document;

/**
 * Root class for documents assets collectors.
 */
abstract class Collector
{
	/**
	 * Collected assets
	 *
	 * @var array
	 */
	protected $collected = array();

	/**
	 * Wheter the collected assets should be cached.
	 *
	 * @var bool
	 */
	public $use_cache = false;

	/**
	 * Sets the cache policy according to the configuration.
	 */
	public function __construct()
	{
		global $core;

		$this->use_cache = !empty($core->config['cache assets']);
	}

	/*
	public static $assets_paths = array
	(
		'%icanboogie%' => '/home/olivier/Sites/weirdog/www/icybee/framework/ICanBoogie/assets',
		'{icanboogie}' => '/home/olivier/Sites/weirdog/www/icybee/framework/ICanBoogie/assets',
		'%icybee%' => '/home/olivier/Sites/weirdog/www/icybee/public',
		'{icybee}' => '/home/olivier/Sites/weirdog/www/icybee/public'
	);
	*/

	/**
	 * Adds an asset to the collection.
	 *
	 * @param string $path Path, or relative path to the asset.
	 * @param int $weight Weight of the asset in the collection.
	 * @param string|null $root Root used to resolve the asset path into a URL.
	 *
	 * @return WdDocumentCollector Return the object itself for chainable calls.
	 */
	public function add($path, $weight=0, $root=null)
	{
		/*
		if ($path{0} == '%' || $path{0} == '{')
		{
			$path = strtr($path, self::$assets_paths);
		}
		*/

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

	abstract public function cache_construct(FileCache $cache, $key, array $userdata);
}

namespace ICanBoogie\Collector;

use ICanBoogie\FileCache;

/**
 * Collector for CSS assets.
 */
class CSS extends \ICanBoogie\Collector
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
				$root = \ICanBoogie\DOCUMENT_ROOT;

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

var document_cached_css_assets = $list;

</script>

EOT;

				}
			}
		}
		catch (\Exception $e) { echo $e; }

		#
		# default ouput
		#

		$rc = '';

		foreach ($collected as $url)
		{
			$rc .= '<link type="text/css" href="' . wd_entities($url) . '" rel="stylesheet" />' . PHP_EOL;
		}

		return $rc;
	}

	public function cache_construct(FileCache $cache, $key, array $userdata)
	{
		$args = func_get_args();

		list($collected) = $userdata;

		$rc = '/* Compiled CSS file generated by ' . __CLASS__ . ' */' . PHP_EOL . PHP_EOL;

		foreach ($collected as $url)
		{
			$contents = file_get_contents(\ICanBoogie\DOCUMENT_ROOT . $url);
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
class JS extends \ICanBoogie\Collector
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
			$root = \ICanBoogie\DOCUMENT_ROOT;
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
				$root = \ICanBoogie\DOCUMENT_ROOT;

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
		catch (\Exception $e) { echo $e; }

		#
		# default ouput
		#

		$rc = '';

		foreach ($collected as $url)
		{
			$rc .= '<script type="text/javascript" src="' . wd_entities($url) . '"></script>' . PHP_EOL;
		}

		return $rc;
	}

	public function cache_construct(FileCache $cache, $key, array $userdata)
	{
		$args = func_get_args();

		list($collected) = $userdata;

		$rc = '';

		foreach ($collected as $url)
		{
			$rc .= file_get_contents($_SERVER['DOCUMENT_ROOT'] . $url);
		}

		$list = json_encode($collected);
		$class = __CLASS__;

		$rc = <<<EOT
//
// Compiled Javascript file generated by $class
//

var document_cached_js_assets = $list;

// BEGIN

EOT

		. $rc;

		file_put_contents(getcwd() . '/' . $key, $rc);

		return $key;
	}
}