<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$file = dirname(__DIR__) . '/Brickrouge.phar';
$phar = new Phar($file);

$phar->buildFromDirectory(__DIR__);
$phar->setStub(file_get_contents('phar.stub.php', true));

if (Phar::canCompress(Phar::GZ))
{
	$phar->compressFiles(Phar::GZ);
}
else if (Phar::canCompress(Phar::BZ2))
{
	$phar->compressFiles(Phar::BZ2);
}

echo "Phar created: $file" . PHP_EOL;
