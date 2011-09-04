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
 * @var string The ROOT directory of the BrickRouge framework.
 */
define('BrickRouge\ROOT', rtrim(__DIR__, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

/**
 * @var string Path to the BrickRouge's assets directory.
 */
define('BrickRouge\ASSETS', ROOT . 'assets' . DIRECTORY_SEPARATOR);

/**
 * @var string Version string of the BrickRouge framework.
 */
define('BrickRouge\VERSION', '1.0.0-dev (2011-09-04)');