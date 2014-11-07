<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Brickrouge;

use Brickrouge\Element;

class DatasetTest extends \PHPUnit_Framework_TestCase
{
	private $element;

	public function setUp()
	{
		$this->element = new Element('div', [

			'class' => 'testing',

			'data-one' => 'one',
			'data-two' => 'two',
			'data-three' => 'three',
			'data-true' => true,
			'data-false' => false,
			'data-null' => null,
			'data-numeric' => 1

		]);
	}

	public function test_get_same()
	{
		$d1 = $this->element->dataset;
		$d2 = $this->element->dataset;

		$this->assertInstanceOf('Brickrouge\Dataset', $d1);
		$this->assertInstanceOf('Brickrouge\Dataset', $d2);
		$this->assertEquals(spl_object_hash($d1), spl_object_hash($d2));
	}

	public function testRendering()
	{
		$rendered = (string) $this->element;

		$this->assertEquals(<<<EOT
<div class="testing" data-one="one" data-two="two" data-three="three" data-true="1" data-false="0" data-numeric="1" />
EOT
		, $rendered);
	}

	public function testTraversing()
	{
		$str = '';

		foreach ($this->element->dataset as $property => $value)
		{
			$str .= '#' . $property;
		}

		$this->assertEquals(<<<EOT
#one#two#three#true#false#null#numeric
EOT
		, $str);
	}

	public function testMirroring()
	{
		$this->element->dataset['mirror-string'] = 'mirror';
		$this->element->dataset['mirror-null'] = null;
		$this->element->dataset['mirror-false'] = false;
		$this->element->dataset['mirror-true'] = true;

		$this->assertEquals('mirror', $this->element->dataset['mirror-string']);
		$this->assertEquals('mirror', $this->element['data-mirror-string']);
		$this->assertEquals(null, $this->element->dataset['mirror-null']);
		$this->assertEquals(null, $this->element['data-mirror-null']);
		$this->assertEquals(false, $this->element->dataset['mirror-false']);
		$this->assertEquals(false, $this->element['data-mirror-false']);
		$this->assertEquals(true, $this->element->dataset['mirror-true']);
		$this->assertEquals(true, $this->element['data-mirror-true']);

		unset($this->element->dataset['mirror-string']);

		$this->assertEquals(null, $this->element->dataset['mirror-string']);
		$this->assertEquals(null, $this->element['data-mirror-string']);
	}

	public function testArrayConversion()
	{
		$array = $this->element->dataset->to_a();

		$this->assertEquals([

			'one' => 'one',
			'two' => 'two',
			'three' => 'three',
			'true' => true,
			'false' => false,
			'null' => null,
			'numeric' => 1


		], $array);
	}
}
