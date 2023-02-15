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

use Exception;
use ICanBoogie\FileCache;

/**
 * Root class for documents assets collectors.
 */
abstract class AssetsCollector
{
    /**
     * Collected assets
     *
     * @var array<string, int>
     *     Where _key_ is a path to an asset and _value_ its weight.
     */
    private array $collected = [];

    /**
     * Whether the collected assets should be cached.
     */
    public bool $use_cache = false;

    /**
     * Sets the cache policy according to the configuration.
     */
    public function __construct(bool $use_cache = false)
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
     * @return $this Return the object itself for chainable calls.
     * @throws Exception when the URL cannot be resolved.
     */
    public function add(string $path, int $weight = 0, string $root = null): self
    {
        $url = Document::resolve_url($path, $root);

        $this->collected[$url] = $weight;

        return $this;
    }

    /**
     * Returns the collected assets as an array of URL.
     *
     * @return string[] The paths, sorted.
     */
    public function get(): array
    {
        $sorted = \ICanBoogie\sort_by_weight($this->collected, function ($v) {
            return $v;
        });

        return array_keys($sorted);
    }

    /**
     * Clears the collected assets.
     */
    public function clear(): void
    {
        $this->collected = [];
    }

    /**
     * Construct cache entry.
     *
     * @param array<mixed> $userdata
     *
     * @deprecated
     */
    abstract public function cache_construct(FileCache $cache, string $key, array $userdata): string;
}
