<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge;

/**
 * This class is a subset of the ICanBoogie\Object class.
 */
class Object
{
	/**
	 * Returns the value of an innaccessible property.
	 *
	 * Multiple callbacks are tried in order to retrieve the value of the property:
	 *
	 * 1. `__volatile_get_<property>`: Get and return the value of the property.The callback may
	 * not be defined by the object's class, but one can extend the class using the mixin features
	 * of the FObject class.
	 * 2. `__get_<property>`: Get, set and return the value of the property. Because the property
	 * is set, the callback is only called once. The callback may not be defined by the object's
	 * class, but one can extend the class using the mixin features of the FObject class.
	 *
	 * @param string $property
	 *
	 * @throws Exception\PropertyNotReadable when the property has a protected or private scope and
	 * no suitable callback to retrieve its value.
	 *
	 * @throws Exception\PropertyNotFound when the property is undefined and there is no suitable
	 * callback to retrieve its values.
	 *
	 * @return mixed The value of the innaccessible property. `null` is returned if the property
	 * could not be retrieved, in which case an exception is raised.
	 */
	public function __get($property)
	{
		$getter = '__volatile_get_' . $property;

		if (method_exists($this, $getter))
		{
			return $this->$getter();
		}

		$getter = '__get_' . $property;

		if (method_exists($this, $getter))
		{
			return $this->$property = $this->$getter();
		}

		#
		#
		#

		$reflexion_class = new \ReflectionClass($this);

		try
		{
			$reflexion_property = $reflexion_class->getProperty($property);

			throw new Exception\PropertyNotReadable(array($property, $this));
		}
		catch (\ReflectionException $e) { }

		$properties = array_keys(get_object_vars($this));

		if ($properties)
		{
			throw new Exception\PropertyNotFound
			(
				format
				(
					'Unknow or unaccessible property %property for object of class %class (available properties: :list).', array
					(
						'property' => $property,
						'class' => get_class($this),
						'list' => implode(', ', $properties)
					)
				)
			);
		}

		throw new Exception\PropertyNotFound(array($property, $this));
	}

	/**
	 * Sets the value of inaccessible properties.
	 *
	 * If the `__volatile_set_<property>` or `__set_<property>` setter methods exists, they are
	 * used to set the value to the property, otherwise the value is set _as is_.
	 *
	 * For performance reason, external setters are not used.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value)
	{
		$setter = '__volatile_set_' . $property;

		if (method_exists($this, $setter))
		{
			return $this->$setter($value);
		}

		$setter = '__set_' . $property;

		if (method_exists($this, $setter))
		{
			return $this->$property = $this->$setter($value);
		}

		$properties = get_object_vars($this);

		if (array_key_exists($property, $properties))
		{
			throw new Exception\PropertyNotWritable(array($property, $this));
		}

		$this->$property = $value;
	}

	/**
	 * Checks if the object has the specified property.
	 *
	 * Unlike the property_exists() function, this method uses all the getters available to find
	 * the property.
	 *
	 * @param string $property The property to check.
	 * @return bool true if the object has the property, false otherwise.
	 */
	public function has_property($property)
	{
		if (property_exists($this, $property))
		{
			return true;
		}

		$getter = '__volatile_get_' . $property;

		if (method_exists($this, $getter))
		{
			return true;
		}

		$getter = '__get_' . $property;

		if (method_exists($this, $getter))
		{
			return true;
		}

		return false;
	}

	/**
	 * Checks whether this object supports the specified method.
	 *
	 * @param string $method Name of the method.
	 * @return bool true if the object supports the method, false otherwise.
	 */
	public function has_method($method)
	{
		return method_exists($this, $method);
	}
}