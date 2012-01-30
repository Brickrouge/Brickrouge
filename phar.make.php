<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function strip_comments($source)
{
	if (!function_exists('token_get_all')) {
		return $source;
	}

	$output = '';
	foreach (token_get_all($source) as $token) {
		if (is_string($token)) {
			$output .= $token;
		} elseif ($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT) {
			$output .= str_repeat("\n", substr_count($token[1], "\n"));
		} else {
			$output .= $token[1];
		}
	}

	return $output;
}

$phar_pathname = dirname(__DIR__) . '/Brickrouge.phar';

if (file_exists($phar_pathname))
{
	unlink($phar_pathname);
}

$do_not_compress = array('gif' => true, 'jpg' => true, 'jpeg' => true, 'png' => true);
$skip = array
(
	__DIR__ . DIRECTORY_SEPARATOR . 'Markfile' => true,
	__DIR__ . DIRECTORY_SEPARATOR . 'phar.make.php' => true,
	__DIR__ . DIRECTORY_SEPARATOR . 'phar.stub.php' => true,
	__DIR__ . DIRECTORY_SEPARATOR . 'README.md' => true
);

$phar = new Phar($phar_pathname);
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->setStub(file_get_contents('phar.stub.php', true));

$phar->startBuffering();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));

$n = 0;
$root_length = strlen(__DIR__ . DIRECTORY_SEPARATOR);

foreach ($rii as $pathname => $file)
{
	if (isset($skip[$pathname]) || strpos($pathname, 'uncompressed') !== false || strpos($pathname, '.less') !== false || preg_match('#/lib/[^.]+\.js$#', $pathname))
	{
		continue;
	}

	echo $pathname . PHP_EOL;

	$extension = $file->getExtension();
	$contents = file_get_contents($pathname);

	if ($extension == 'php')
	{
		$contents = strip_comments(file_get_contents($pathname));
	}

	$pathname = substr($pathname, $root_length);

	$phar[$pathname] = $contents;

	if (empty($do_not_compress[$extension]))
	{
		$phar[$pathname]->compress(Phar::GZ);
	}

	$n++;
}

$phar->stopBuffering();

echo "Phar created: $phar_pathname ($n files, " . round((filesize($phar_pathname) / 1024)) . ' Ko)' . PHP_EOL;
