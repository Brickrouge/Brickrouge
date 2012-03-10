<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

/**
 * This class is a subset of the {@link ICanBoogie\Object} class of the ICanBoogie framework, it
 * is used when the ICanBoogie framework is not available and provides similar features.
 */
class Object
{
	/**
	 * Returns the value of an inaccessible property.
	 *
	 * The following callbacks are tried to retrieve the value of the property:
	 *
	 * 1. `__volatile_get_<property>`: Get and return the value of the property.
	 *
	 * 2. `__get_<property>`: Get, set and return the value of the property. Because the property
	 * is set, the callback might only be called once if the property was previously undefined,
	 * that's because in that case the property is created as _public_. This feature is perfect
	 * for lazy loading.
	 *
	 * An error is triggered if the property is readable only, inaccessible or unknown.
	 *
	 * @param string $property
	 *
	 * @return mixed The value of the inaccessible property. `null` is returned if the property
	 * could not be retrieved, in which case an exception is raised.
	 *
	 * @see http://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
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

		$reflexion_class = new \ReflectionClass($this);

		try
		{
			$reflexion_property = $reflexion_class->getProperty($property);

			trigger_error(\Brickrouge\format('The property %property is not readable for object of class %class.', array('property' => $property, 'class' => get_class($this))));
		}
		catch (\ReflectionException $e) { }

		trigger_error(\Brickrouge\format('Unknown or inaccessible property %property for object of class %class.', array('property' => $property, 'class' => get_class($this))));
	}

	/**
	 * Sets the value of a property.
	 *
	 * If the `__volatile_set_<property>` or `__set_<property>` setter methods exists, they are
	 * used to set the value to the property, otherwise if the property doesn't exists the property
	 * is created as public and its value is set _as is_.
	 *
	 * An error is triggered if one tries to set a inaccessible property.
	 *
	 * @param string $property
	 * @param mixed $value
	 *
	 * @see http://www.php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.members
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
			trigger_error(\Brickrouge\format('The property %property is not writable for object of class %class.', array('property' => $property, 'class' => get_class($this))));

			return;
		}

		$this->$property = $value;
	}

	/**
	 * The method is invoked when the `unset()` function is used on inaccessible properties.
	 *
	 * If the `__unset_<property>` method exists it is used to unset the property.
	 *
	 * @param string $property
	 */
	public function __unset($property)
	{
		$method_name = '__unset_' . $property;

		if (method_exists($this, $method_name))
		{
			$this->$method_name();
		}
	}

	/**
	 * Checks if the object has the specified property.
	 *
	 * Unlike the `property_exists()` function, this method uses all the getters available to find
	 * the property.
	 *
	 * @param string $property The property to check.
	 *
	 * @return bool true if the object has the property, false otherwise.
	 */
	public function has_property($property)
	{
		return property_exists($this, $property) || method_exists($this, '__volatile_get_' . $property) || method_exists($this, '__get_' . $property);
	}
}