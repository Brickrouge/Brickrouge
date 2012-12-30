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
use Brickrouge\Form;
use Brickrouge\RecursiveIterator;

class IteratorsTest extends \PHPUnit_Framework_TestCase
{
	private $children;

	public function setUp()
	{
		$this->children = $children = array
		(
			'one' => new Element
			(
				'div', array
				(
					Element::CHILDREN => array
					(
						'one-one' => new Element
						(
							'div', array
							(
								Element::CHILDREN => array
								(
									'one-one-one' => new Element
									(
										'div'
									)
								)
							)
						)
					)
				)
			),

			'text0' => 'text0',

			'two' => new Element
			(
				'div', array
				(
					Element::CHILDREN => array
					(
						'two-one' => new Element
						(
							'div', array
							(
								Element::CHILDREN => array
								(
									'two-one-one' => new Element
									(
										'div'
									)
								)
							)
						),

						'two-two' => new Element
						(
							'div', array
							(
								Element::CHILDREN => array
								(
									'two-two-one' => new Element
									(
										'div'
									),

									'text220',

									'two-two-two' => new Element
									(
										'div'
									)
								)
							)
						)
					)
				)
			),

			'text1' => 'text1',

			'three' => new Element
			(
				'div', array
				(
					Element::CHILDREN => array
					(
						'three-one' => new Element
						(
							'div'
						),

						'three-two' => new Element
						(
							'div'
						),

						'three-three' => new Element
						(
							'div', array
							(
								Element::CHILDREN => array
								(
									'three-three-one' => new Element
									(
										'div'
									),

									'text330',

									'three-three-two' => new Element
									(
										'div'
									),

									'text330',

									'three-three-three' => new Element
									(
										'div'
									)
								)
							)
						)
					)
				)
			),

			'text3' => 'text3'
		);
	}

	public function testElementIterator()
	{
		$str = '';
		$element = new Element('div', array(Element::CHILDREN => $this->children));

		foreach ($element as $child)
		{
			$str .= '#' . $child['name'];
		}

		$this->assertEquals('#one#two#three', $str);
	}

	public function testElementRecusiveIterator()
	{
		$str = '';
		$element = new Element('div', array(Element::CHILDREN => $this->children));
		$iterator = new \RecursiveIteratorIterator(new RecursiveIterator($element), \RecursiveIteratorIterator::SELF_FIRST);

		foreach ($iterator as $child)
		{
			$str .= '#' . $child['name'];
		}

		$this->assertEquals('#one#one-one#one-one-one#two#two-one#two-one-one#two-two#two-two-one#two-two-two#three#three-one#three-two#three-three#three-three-one#three-three-two#three-three-three', $str);
	}

	public function testFormIterator()
	{
		$str = '';
		$element = new Form(array(Element::CHILDREN => $this->children));

		foreach ($element as $child)
		{
			$str .= '#' . $child['name'];
		}

		$this->assertNotEquals('#one#two#three', $str);
	}

	public function testFormRecusiveIterator()
	{
		$str = '';
		$element = new Form(array(Element::CHILDREN => $this->children));

		foreach ($element as $child)
		{
			$str .= '#' . $child['name'];
		}

		$this->assertEquals('#one#one-one#one-one-one#two#two-one#two-one-one#two-two#two-two-one#two-two-two#three#three-one#three-two#three-three#three-three-one#three-three-two#three-three-three', $str);
	}
}