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

use Brickrouge\Helpers\PublishAssets;
use RuntimeException;
use Throwable;

/**
 * Brickrouge helpers.
 *
 * The following helpers are patchable:
 *
 * - {@link format()}
 * - {@link format_size()}
 * - {@link get_accessible_file()}
 * - {@link get_document()}
 * - {@link normalize()}
 * - {@link render_exception()}
 * - {@link t()}
 *
 * @method static string format(string $str, array $args = [])
 * @method static string format_size(int $size)
 * @method static string get_accessible_file(string $path)
 * @method static Document get_document()
 * @method static string normalize(string $str, string $separator = '-', string $charset = CHARSET)
 * @method static string render_exception(Throwable $exception)
 * @method static string t(string $str, array $args = [], array $options = [])
 */
final class Helpers
{
    /**
     * @var array<string, callable>
     *
     * @uses default_format()
     * @uses default_format_size()
     * @uses default_get_accessible_file()
     * @uses default_get_document()
     * @uses default_normalize()
     * @uses default_render_exception()
     * @uses default_t()
     */
    private static array $mapping = [

        'format' => [ __CLASS__, 'default_format' ],
        'format_size' => [ __CLASS__, 'default_format_size' ],
        'get_accessible_file' => [ __CLASS__, 'default_get_accessible_file' ],
        'get_document' => [ __CLASS__, 'default_get_document' ],
        'normalize' => [ __CLASS__, 'default_normalize' ],
        'render_exception' => [ __CLASS__, 'default_render_exception' ],
        't' => [ __CLASS__, 'default_t' ]

    ];

    /**
     * Calls the callback of a patchable function.
     *
     * @param string $name Name of the function.
     * @param array<mixed> $arguments Arguments.
     *
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return call_user_func_array(self::$mapping[$name], $arguments);
    }

    /**
     * Patches a patchable function.
     *
     * @param string $name Name of the function.
     * @param callable $callback Callback.
     *
     * @throws RuntimeException is attempt to patch an undefined function.
     */
    public static function patch(string $name, callable $callback): void
    {
        if (empty(self::$mapping[$name])) {
            throw new RuntimeException("Undefined patchable: $name.");
        }

        self::$mapping[$name] = $callback;
    }

    /*
     * fallbacks
     */

    /**
     * This method is the fallback for the {@link format()} function.
     *
     * @param array<int|string, mixed> $args
     *
     * @see \Brickrouge\format()
     */
    private static function default_format(string $str, array $args = []): string
    {
        return \ICanBoogie\format($str, $args);
    }

    /**
     * This method is the fallback for the {@link format_size()} function.
     *
     * @see \Brickrouge\format_size()
     */
    private static function default_format_size(int $size): string
    {
        if ($size < 1024) {
            $str = ":size\xC2\xA0B";
        } elseif ($size < 1024 * 1024) {
            $str = ":size\xC2\xA0KB";
            $size = $size / 1024;
        } elseif ($size < 1024 * 1024 * 1024) {
            $str = ":size\xC2\xA0MB";
            $size = $size / (1024 * 1024);
        } else {
            $str = ":size\xC2\xA0GB";
            $size = $size / (1024 * 1024 * 1024);
        }

        return t($str, [ ':size' => round($size) ]);
    }

    /**
     * This method is the fallback for the {@link normalize()} function.
     *
     * @param string $str
     * @param non-empty-string $separator
     * @param string $charset
     *
     * @return string
     *
     * @see \Brickrouge\normalize()
     */
    private static function default_normalize(string $str, string $separator = '-', $charset = CHARSET): string
    {
        $str = str_replace('\'', '', $str);

        $str = htmlentities($str, ENT_NOQUOTES, $charset);
        $str = preg_replace('#&([A-za-z])(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $str);
        $str = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $str);
        $str = preg_replace('#&[^;]+;#', '', $str);

        $str = strtolower($str);
        $str = preg_replace('#[^a-z0-9]+#', $separator, $str);
        $str = trim($str, $separator);

        return $str;
    }

    /**
     * This method is the fallback for the {@link t()} function.
     *
     * We usually rely on the ICanBoogie framework I18n features to translate our string, if it is
     * not available we simply format the string using the {@link format()} function.
     *
     * @param array<int|string, mixed> $args
     *
     * @see \Brickrouge\t()
     */
    private static function default_t(string $str, array $args = []): string
    {
        return format($str, $args);
    }

    /**
     * This method is the fallback for the {@link get_document()} function.
     *
     * @see \Brickrouge\get_document()
     */
    private static function default_get_document(): Document
    {
        static $document;

        return $document ??= new Document();
    }

    /**
     * This method is the fallback for the {@link render_exception()} function.
     *
     * @see \Brickrouge\render_exception()
     */
    private static function default_render_exception(Throwable $exception): string
    {
        return (string) $exception;
    }

    /**
     * This method is the fallback for the {@link get_accessible_file()} function.
     *
     * @param string $path Absolute path to the web inaccessible file.
     *
     * @return string The pathname of the replacement.
     *
     * @see \Brickrouge\get_accessible_file()
     */
    private static function default_get_accessible_file(string $path): string
    {
        static $publish_assets;

        $publish_assets ??= new PublishAssets(ACCESSIBLE_ASSETS);

        return $publish_assets($path);
    }
}
