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
 * Custom data attributes are intended to store custom data private to the page or application,
 * for which there are no more appropriate attributes or elements.
 *
 * @see http://www.w3.org/TR/html5/elements.html#embedding-custom-non-visible-data-with-the-data-attributes
 */
class Dataset implements \ArrayAccess, \IteratorAggregate
{
	static protected function serialize_property($property)
	{
		return 'data-' . $property;
	}

	static protected function unserialize_property($property)
	{
		return substr($property, 5);
	}

	/**
	 * The target element.
	 *
	 * @var Element
	 */
	protected $element;

	/**
	 * Constructor.
	 *
	 * @param Element $element The target element.
	 * @param array $properties[optional] The initial properties of the dataset.
	 */
	public function __construct(Element $element, array $properties=array())
	{
		$this->element = $element;

		foreach ($properties as $property => $value)
		{
			$this[$property] = $value;
		}
	}

	/**
	 * Sets the value of a property.
	 *
	 * The attribute corresponding to the property is set.
	 *
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($property, $value)
	{
		$this->element->offsetSet(self::serialize_property($property), $value);
	}

	/**
	 * Returns the value of a property,
	 *
	 * The value is gotten from the attribute corresponding to the property.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($property, $default=null)
	{
		return $this->element->offsetGet(self::serialize_property($property), $default);
	}

	public function offsetExists($property)
	{
		return $this->element->offsetExists(self::serialize_property($property));
	}

	public function offsetUnset($property)
	{
		return $this->element->offsetUnset(self::serialize_property($property));
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->to_a());
	}

	/**
	 * Returns an array representation of the dataset.
	 *
	 * @return array[string]mixed
	 */
	public function to_a()
	{
		$properties = array();

		foreach ($this->element->attributes as $attribute => $value)
		{
			if (strpos($attribute, 'data-') !== 0)
			{
				continue;
			}

			$properties[self::unserialize_property($attribute)] = $value;
		}

		return $properties;
	}
}

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

/**
 * Recursive iterator used to traverse in depth {@link Element} children.
 */
class RecursiveIterator extends Iterator implements \RecursiveIterator
{
	public function hasChildren()
	{
		$current = $this->current();

		return !empty($current->children);
	}

	public function getChildren()
	{
		$current = $this->current();

		return new static($current);
	}
}