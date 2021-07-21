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
 * Root class for documents assets collectors.
 */
abstract class AssetsCollector
{
    /**
     * Collected assets
     *
     * @var array
     */
    protected $collected = [];

    /**
     * Whether the collected assets should be cached.
     *
     * @var bool
     */
    public $use_cache = false;

    /**
     * Sets the cache policy according to the configuration.
     *
     * @param bool $use_cache
     */
    public function __construct($use_cache = false)
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
    public function add($path, $weight = 0, $root = null)
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
        $sorted = \ICanBoogie\sort_by_weight($this->collected, function ($v) {

            return $v;
        });

        return array_keys($sorted);
    }

    /**
     * Clears the collected assets.
     */
    public function clear()
    {
        $this->collected = [];
    }

    /**
     * Construct cache entry.
     *
     * @param FileCache $cache
     * @param string $key
     * @param array $userdata
     *
     * @return mixed
     *
     * @deprecated
     */
    abstract public function cache_construct(FileCache $cache, $key, array $userdata);
}
