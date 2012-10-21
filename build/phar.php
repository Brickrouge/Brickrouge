<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$package_name = 'Brickrouge';

$exclude = array
(
	'.*\.less',
	'.*\.md',
	'.*-uncompressed\..*',
	'.git/.*',
	'.gitignore',
	'.travis.yml',
	'build/.*',
	'lib/.*\.js',
	'Makefile',
	'phpunit.xml.dist',
	'README.md',
	'tests/.*'
);

$do_not_compress = array('gif' => true, 'jpg' => true, 'jpeg' => true, 'png' => true);

/*
 * Do not edit the following lines.
 */

function strip_comments($source)
{
	if (!function_exists('token_get_all'))
	{
		return $source;
	}

	$output = '';

	foreach (token_get_all($source) as $token)
	{
		if (is_string($token))
		{
			$output .= $token;
		}
		else if ($token[0] == T_COMMENT || $token[0] == T_DOC_COMMENT)
		{
			$output .= str_repeat("\n", substr_count($token[1], "\n"));
		}
		else
		{
			$output .= $token[1];
		}
	}

	return $output;
}

$dir = dirname(__DIR__);

chdir($dir);

$phar_pathname = dirname($dir) . "/{$package_name}.phar";

if (file_exists($phar_pathname))
{
	unlink($phar_pathname);
}

$phar = new Phar($phar_pathname);
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->setStub(<<<EOT
<?php

define('{$package_name}\ROOT', 'phar://' . __FILE__ . DIRECTORY_SEPARATOR);

require_once {$package_name}\ROOT . 'startup.php';

__HALT_COMPILER();
EOT
);

$phar->startBuffering();

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS));

$n = 0;
$root_length = strlen($dir . DIRECTORY_SEPARATOR);

array_walk($exclude, function(&$v) { $v = "~^{$v}$~"; });

function is_excluded($pathname)
{
	global $exclude;

	foreach ($exclude as $pattern)
	{
		if (preg_match($pattern, $pathname))
		{
			return true;
		}
	}
}

foreach ($rii as $pathname => $file)
{
	$extension = $file->getExtension();
	$relative_pathname = substr($pathname, $root_length);

	if (is_excluded($relative_pathname))
	{
		continue;
	}

	echo $relative_pathname . PHP_EOL;

	$contents = file_get_contents($pathname);

	if ($extension == 'php')
	{
		$contents = strip_comments($contents);
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
