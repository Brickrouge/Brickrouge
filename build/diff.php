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

function strip($str)
{
	$parts = preg_split('#//<(/?brickrouge)>#', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

	# 0: before
	# 1: brickrouge
	# 2: content
	# 3: /brickrouge

	$content = '';

	foreach ($parts as $i => $part)
	{
		if ($i % 4 != 2)
		{
			continue;
		}

		$content .= $part;
	}

	return $content;
}

$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR;
$tmp_path = $_SERVER['argv'][1];

$iterator = new \DirectoryIterator($path);
$filter = new \RegexIterator($iterator, '#\.less$#');

$except = array('brickrouge.less', 'mixins.less', 'variables.less');

foreach ($filter as $file)
{
	$filename = $file->getFilename();
	$pathname = $file->getPathname();

	if (in_array($filename, $except))
	{
		copy($pathname, $tmp_path . $filename);
	}
	else
	{
		$content = file_get_contents($pathname);
		$content = strip($content);

		file_put_contents($tmp_path . $filename, $content);
	}
}