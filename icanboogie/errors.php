<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

class Errors implements \ArrayAccess, \Countable, \Iterator
{
	protected $errors=array(null => array());

	/**
	 * Checks if an error is defined for an attribute.
	 *
	 * Example:
	 *
	 * $e = new Errors();
	 * $e['username'] = 'Funny username';
	 * var_dump(isset($e['username']);
	 * #=> true
	 * var_dump(isset($e['password']);
	 * #=> false
	 *
	 * @return boolean true if an error is defined for the specified attibute, false otherwise.
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($attribute)
	{
		return isset($this->errors[$attribute]);
	}

	/**
	 * Returns error messages.
	 *
	 * Example:
	 *
	 * $e = new Errors();
	 * var_dump($e['password']);
	 * #=> null
	 * $e['password'] = 'Invalid password';
	 * var_dump($e['password']);
	 * #=> 'Invalid password'
	 * $e['password'] = 'Ugly password';
	 * var_dump($e['password']);
	 * #=> array('Invalid password', 'Ugly password')
	 *
	 * @return string|array|null Return the global error messages or the error messages attached
	 * to an attribute. If there is only one message a string is returned, otherwise an array
	 * with all the messages is returned. null is returned if there is no message defined.
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($attribute)
	{
		if (empty($this->errors[$attribute]))
		{
			return null;
		}

		$messages = $this->errors[$attribute];

		return count($messages) > 1 ? $messages : current($messages);
	}

	/**
	 * Adds an error message.
	 *
	 * Example:
	 *
	 * $e = new Errors();
	 * $e['password'] = 'Invalid password';
	 * $e[] = 'Requires authentication';
	 *
	 * @param string|null attribute If null, the message is considered as a general error message
	 * instead of an attribute messge.
	 * @param string message The error message.
	 *
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($attribute, $message)
	{
		$this->errors[$attribute][] = $message;
	}

	/**
	 * Removes error messages.
	 *
	 * @param string|null attribute If null, general message are removed, otherwise the message
	 * attached to the attribute are removed.
	 *
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($attribute)
	{
		unset($this->errors[$attribute]);
	}

	/**
	 * Returns the number of errors defined.
	 *
	 * Example:
	 *
	 * $e = new Errors();
	 * $e['username'] = 'Funny user name';
	 * $e['password'] = 'Weak password';
	 * $e['password'] = 'should have at least one digit';
	 * count($e);
	 * #=> 3
	 *
	 * @see Countable::count()
	 */
	public function count()
	{
		$n = 0;

		foreach ($this->errors as $errors)
		{
			$n += count($errors);
		}

		return $n;
	}

	private $i;
	private $ia;

	public function current()
	{
		return $this->ia[$this->i][1];
	}

	public function next()
	{
		++$this->i;
	}

	public function key()
	{
		return $this->ia[$this->i][0];
	}

	public function valid()
	{
		return isset($this->ia[$this->i]);
	}

	public function rewind()
	{
		$this->i = 0;
		$ia = array();

		foreach ($this->errors as $attribute => $errors)
		{
			foreach ($errors as $error)
			{
				$ia[] = array($attribute, $error);
			}
		}

		$this->ia = $ia;
	}

	/**
	 * Iterates through errors using the specified callback.
	 *
	 * Example:
	 *
	 * $e = new Errors();
	 * $e['username'] = 'Funny user name';
	 * $e['password'] = 'Weak password';
	 *
	 * $e->each
	 * (
	 *     function($attribute, $message)
	 *     {
	 *         echo "$attribute => $message<br />";
	 *     }
	 * );
	 *
	 * @param mixed $callback
	 */
	public function each($callback)
	{
		foreach ($this->errors as $attribute => $errors)
		{
			foreach ($errors as $error)
			{
				call_user_func($callback, $attribute, $error);
			}
		}
	}

	/**
	 * Clears the errors.
	 */
	public function clear()
	{
		$this->errors = array(null => array());
	}
}