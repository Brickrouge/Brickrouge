<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$file = dirname(__DIR__) . '/BrickRouge.phar';
$phar = new Phar($file);

$phar->buildFromDirectory(__DIR__);
$phar->setStub(file_get_contents('phar.stub.php', true));

echo "Phar created: $file" . PHP_EOL;
