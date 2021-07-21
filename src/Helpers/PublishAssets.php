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
    private $destination;

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
        $source = dirname($pathname);
        $destination = $this->destination . sha1($source);

        if (!file_exists($destination)) {
            $parent = dirname($destination);

            if (!file_exists($parent)) {
                throw new \LogicException("Unable to create symlink, parent directory is missing: $parent");
            }

            symlink($source, $destination);
        }

        return $destination . DIRECTORY_SEPARATOR . basename($pathname);
    }
}
