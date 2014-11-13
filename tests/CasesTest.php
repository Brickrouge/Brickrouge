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

class CasesTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider provide_test_case
	 */
	public function test_case($pathname)
	{
		ob_start();

		include $pathname;

		$html = ob_get_clean();
		$expected_pathname = preg_replace('#\.php$#', '.html', $pathname);
		$expected = file_get_contents($expected_pathname);

		$this->assertEquals($expected, $html);
	}

	public function provide_test_case()
	{
		$di = new \DirectoryIterator(__DIR__ . '/cases');
		$iterator = new \RegexIterator($di, '#\.php$#');
		$cases = [];

		foreach ($iterator as $file)
		{
			$cases[] = [ $file->getPathname() ];
		}

		return $cases;
	}
}
