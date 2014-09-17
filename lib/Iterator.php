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

/**
 * An iterator used to traverse {@link Element} descendant.
 *
 * The iterator collects all descendant elements excluding non {@link Element} instances.
 */
class Iterator implements \Iterator
{
	protected $children = array();
	protected $left;

	public function __construct(Element $element)
	{
		$children = array();

		foreach ($element->children as $key => $child)
		{
			if (!($child instanceof Element))
			{
				continue;
			}

			$children[$key] = $child;
		}

		$this->children = $children;
	}

	public function rewind()
	{
		reset($this->children);

		$this->left = count($this->children);
	}

	public function next()
	{
		next($this->children);

		$this->left--;
	}

	public function valid()
	{
		return !!$this->left;
	}

	public function key()
	{
		return key($this->children);
	}

	public function current()
	{
		return current($this->children);
	}
}