<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Helpers;

/**
 * Publish assets to a destination directory.
 */
class PublishAssets
{
	/**
	 * @var string
	 */
	protected $destination;

	/**
	 * @param string $destination
	 */
	public function __construct($destination)
	{
		$this->destination = rtrim($destination, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}

	/**
	 * @param string $pathname
	 *
	 * @return string
	 */
	public function __invoke($pathname)
	{
		$source = dirname($pathname) . DIRECTORY_SEPARATOR;
		$destination = $this->destination . sha1($source) . DIRECTORY_SEPARATOR;

//		if (!file_exists($destination) || filectime($source) > filectime($destination))
		{
			$this->copy_recursive($source, $destination);
		}

		return $destination . basename($pathname);
	}

	/**
	 * Copy file recursively when needed.
	 *
	 * @param string $source
	 * @param string $destination
	 */
	protected function copy_recursive($source, $destination)
	{
		$this->ensure_destination($destination);

		foreach (new \DirectoryIterator($source) as $file)
		{
			if ($file->isDot())
			{
				continue;
			}

			$filename = $file->getFilename();

			if ($file->isDir())
			{
				$this->copy_recursive(
					$source . $filename . DIRECTORY_SEPARATOR,
					$destination . $filename . DIRECTORY_SEPARATOR
				);

				continue;
			}

			$d = $destination . $filename;

//			if (!file_exists($d) || filectime($d) < $file->getCTime())
			{
				copy($file->getPathname(), $d);
			}
		}
	}

	/**
	 * Ensures the destination folder exists.
	 *
	 * @param string $destination
	 */
	protected function ensure_destination($destination)
	{
		if (file_exists($destination))
		{
			return;
		}

		mkdir($destination, 0777, true);
	}
}
