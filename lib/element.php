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

use ICanBoogie\Errors;

/**
 * An HTML element.
 *
 * @property string $class Assigns a class name or set of class names to an element. Any number of
 * elements may be assigned the same class name or names. Multiple class names must be separated
 * by white space characters.
 * @property string $id Assigns a name to an element. This name mush be unique in a document.
 *
 * @see http://dev.w3.org/html5/spec/Overview.html#embedding-custom-non-visible-data-with-the-data-attributes
 */
class Element extends \ICanBoogie\Object implements \ArrayAccess, \RecursiveIterator
{
	#
	# special elements
	#

	/**
	 * Custom type used to create checkbox elements.
	 *
	 * @var string
	 */
	const TYPE_CHECKBOX = '#checkbox';

	/**
	 * Custom type used to create checkbox group elements.
	 *
	 * @var string
	 */
	const TYPE_CHECKBOX_GROUP = '#checkbox-group';

	/**
	 * Custom type used to create file elements.
	 *
	 * @var string
	 */
	const TYPE_FILE = '#file';

	/**
	 * Custom type used to create radio elements.
	 *
	 * @var string
	 */
	const TYPE_RADIO = '#radio';

	/**
	 * Custom type used to create radio group elements.
	 *
	 * @var string
	 */
	const TYPE_RADIO_GROUP = '#radio-group';

	#
	# special tags
	#

	/**
	 * Used to define the children of an element.
	 *
	 * @var string
	 */
	const CHILDREN = '#children';

	/**
	 * Custom data attributes are intended to store custom data private to the page or application,
	 * for which there are no more appropriate attributes or elements. The dataset property
	 * provides convenient accessors for all the data-* attributes on an element.
	 *
	 * @var string
	 *
	 * @see http://www.w3.org/TR/html5/elements.html#embedding-custom-non-visible-data-with-the-data-attributes
	 */
	const DATASET = '#dataset';

	/**
	 * Used to define the default value of an element.
	 *
	 * The default value is added to the dataset as 'default-value'.
	 *
	 * @var string
	 */
	const DEFAULT_VALUE = '#default-value';

	/**
	 * Used to define the description block of an element.
	 *
	 * @var string
	 *
	 * @see Element::decorate_with_description()
	 */
	const DESCRIPTION = '#description';
	const FILE_WITH_LIMIT = '#element-file-with-limit';
	const FILE_WITH_REMINDER = '#element-file-with-reminder';

	/**
	 * Used to define the group of an element.
	 *
	 * @var string
	 */
	const GROUP = '#group';

	/**
	 * Used to define the groups that can be used by children elements.
	 *
	 * @var string
	 */
	const GROUPS = '#groups';

	/**
	 * Used to define the inline help of an element.
	 *
	 * @var string
	 *
	 * @see Element::decorate_with_inline_help()
	 */
	const INLINE_HELP = '#inline-help';

	/**
	 * Used to define the inner HTML of an element. If the value of the tag is null, the markup
	 * will be self-closing.
	 *
	 * @var string
	 */
	const INNER_HTML = '#inner-html';

	/**
	 * Used to define the label of an element.
	 *
	 * @var string
	 *
	 * @see Element::decorate_with_label()
	 */
	const LABEL = '#element-label';

	/**
	 * Used to define the position of the label. Possible positions are "before", "after" and
	 * "above". Defaults to "after".
	 *
	 * @var string
	 */
	const LABEL_POSITION = '#element-label-position';
	const LABEL_SEPARATOR = '#element-label-separator';
	const LABEL_MISSING = '#element-label-missing';

	/**
	 * Used to define the legend of an element. If the legend is defined the element is wrapped
	 * into a fieldset when it is rendered.
	 *
	 * @var string
	 *
	 * @see Element::decorate_with_legend()
	 */
	const LEGEND = '#element-legend';

	/**
	 * Used to define the required state of an element.
	 *
	 * @var string
	 *
	 * @see Form::validate()
	 * @see http://dev.w3.org/html5/spec/Overview.html#the-required-attribute
	 */
	const REQUIRED = 'required';

	/**
	 * Used to define the options of the following element types: "select", TYPE_RADIO_GROUP
	 * and TYPE_CHECKBOX_GROUP.
	 *
	 * @var string
	 */
	const OPTIONS = '#element-options';

	/**
	 * Used to define which options are disabled.
	 *
	 * @var string
	 */
	const OPTIONS_DISABLED = '#element-options-disabled';

	/**
	 * Used to define the state of the element: 'success', 'warning', 'error'.
	 *
	 * @var string
	 */
	const STATE = '#state';

	/**
	 * Used to define the validator of an element. The validator is defined using an array made of
	 * a callback and a possible userdata array.
	 *
	 * @var string
	 */
	const VALIDATOR = '#validator';
	const VALIDATOR_OPTIONS = '#validator-options';

	/**
	 * Use to define the weight of an element. This attribute can be used to reorder children when
	 * a parent element is rendered.
	 *
	 * @var string
	 *
	 * @see Element::get_ordered_children()
	 */
	const WEIGHT = '#weight';

	static private $inputs = array('button', 'form', 'input', 'option', 'select', 'textarea');
	static private $has_attribute_disabled = array('button', 'input', 'optgroup', 'option', 'select', 'textarea');
	static private $has_attribute_value = array('button', 'input', 'option');
	static private $has_attribute_required = array('input', 'select', 'textarea');

	/**
	 * Type if the element, as provided during __construct().
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Tag name of the rendered HTML element.
	 *
	 * @var string
	 */
	protected $tag_name;

	/**
	 * An array containing the children of the element.
	 *
	 * @var array
	 */
	public $children = array();

	/**
	 * Dataset of the element.
	 *
	 * @var array[]string
	 */
	public $dataset = array();

	/**
	 * Tags of the element, including HTML attributes and custom tags.
	 *
	 * @var array[string]mixed
	 */
	protected $tags = array();

	/**
	 * Inner HTML of the element.
	 *
	 * @var string|null
	 *
	 * @see Element::render_inner_html()
	 */
	protected $inner_html;

	/**
	 * Constructor.
	 *
	 * @param string $type Type of the element, it can be one of the custom types (TYPE_*) or any
	 * HTML type.
	 *
	 * @param array $tags HTML attributes and custom tags.
	 */
	public function __construct($type, $tags=array())
	{
		if ($tags === null)
		{
			$tags = array();
		}

		$this->type = $type;
		$this->tags = $tags;

		#
		# children first
		#

		if (!empty($tags[self::CHILDREN]))
		{
			$this->children = array();
			$this->add_children($tags[self::CHILDREN]);

			unset($tags[self::CHILDREN]);
		}

		#
		# DATASET before "data-*"
		#

		if (!empty($tags[self::DATASET]))
		{
			$this[self::DATASET] = $tags[self::DATASET];
		}

		#
		# prepare special elements
		#

		switch ((string) $type)
		{
			case self::TYPE_CHECKBOX:
			case self::TYPE_RADIO:
			{
				static $translate = array
				(
					self::TYPE_CHECKBOX => array('input', 'checkbox'),
					self::TYPE_RADIO => array('input', 'radio'),
				);

				$this->tag_name = $translate[$type][0];
				$tags['type'] = $translate[$type][1];
			}
			break;

			case self::TYPE_CHECKBOX_GROUP:
			{
				$this->tag_name = 'div';
			}
			break;

			case self::TYPE_FILE:
			{
				$this->tag_name = 'input';

				$tags['type'] = 'file';

				$tags += array('size' => 40);
			}
			break;

			case self::TYPE_RADIO_GROUP:
			{
				$this->tag_name = 'div';
			}
			break;

			case 'textarea':
			{
				$this->tag_name = 'textarea';
				$this->inner_html = '';

				$tags += array('rows' => 10, 'cols' => 76);
			}
			break;

			default:
			{
				$this->tag_name = $type;
			}
			break;
		}

		$this->set($tags);

		if ((string) $type == self::TYPE_CHECKBOX_GROUP)
		{
			$this->add_class('checkbox-group');
		}
		else if ((string) $type == self::TYPE_RADIO_GROUP)
		{
			$this->add_class('radio-group');
		}
	}

	/*
	 * ArrayAccess implement.
	 */

	/**
	 * @param offset
	 */
	public function offsetExists($offset)
	{
		if (strpos($offset, 'data-') === 0)
		{
			return isset($this->dataset[substr($offset, 5)]);
		}

		return isset($this->tags[$offset]);
	}

	/**
	 * @param offset
	 */
	public function offsetGet($offset, $default=null)
	{
		if (strpos($offset, 'data-') === 0)
		{
			$offset = substr($offset, 5);

			return isset($this->dataset[$offset]) ? $this->dataset[$offset] : $default;
		}

		return $this->offsetExists($offset) ? $this->tags[$offset] : $default;
	}

	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet($offset, $value)
	{
		if (strpos($offset, 'data-') === 0)
		{
			$this->dataset[substr($offset, 5)] = $value;

			return;
		}

		switch ($offset)
		{
			case self::CHILDREN:
			{
				$this->children = array();
				$this->add_children($value);
			}
			break;

			case self::DATASET:
			{
				$this->dataset = $value;
			}
			break;

			case self::INNER_HTML:
			{
				$this->inner_html = $value;
			}
			break;

			case 'class':
			{
				$this->class = $value;
			}
			break;

			case 'id':
			{
				unset($this->id);
			}
			break;
		}

		$this->tags[$offset] = $value;
	}

	/**
	 * @param offset
	 */
	public function offsetUnset($offset)
	{
		if (strpos($offset, 'data-') === 0)
		{
			unset($this->dataset[substr($offset, 5)]);

			return;
		}

		unset($this->tags[$offset]);
	}

	/*
	 * RecursiveIterator implementation.
	 */

	protected $recursive_iterator_position;
	protected $recursive_iterator_keys;

	public function current()
	{
		return $this->children[$this->recursive_iterator_keys[$this->recursive_iterator_position]];
	}

	public function key()
	{
		return $this->recursive_iterator_keys[$this->recursive_iterator_position];
	}

	public function next()
	{
		return $this->recursive_iterator_position++;
	}

	/**
	 * Creates an array with instanced from the class, descarting children provided as
	 * strings.
	 *
	 * @see RecursiveIterator::rewind()
	 */
	public function rewind()
	{
		$keys = array();

		foreach ($this->children as $key => $child)
		{
			if ($child instanceof self)
			{
				$keys[] = $key;
			}
		}

		$this->recursive_iterator_keys = $keys;
		$this->recursive_iterator_position = 0;
	}

	public function valid()
	{
		return isset($this->recursive_iterator_keys[$this->recursive_iterator_position]);
	}

	public function getChildren()
	{
		return $this->current();
	}

	public function hasChildren()
	{
		return $this->getChildren() instanceof self;
	}

	/*
	 * End of the RecursiveIterator implementation.
	 */

	/**
	 * Returns a unique element id string.
	 *
	 * @return string
	 */
	public static function auto_element_id()
	{
		return 'autoid--' . self::$auto_element_id++;
	}

	static protected $auto_element_id = 1;

	/**
	 * Returns the element's id.
	 *
	 * If the element's id is empty, a unique id is generated and added to its tags.
	 *
	 * @return string
	 */
	protected function __get_id()
	{
		$id = $this['id'];

		if (!$id)
		{
			$name = $this['name'];

			if ($name)
			{
				$id = 'autoid--' . normalize($name);
			}
			else
			{
				$id = self::auto_element_id();
			}

			$this['id'] = $id;
		}

		return $id;
	}

	/**
	 * The set() method is used to set, unset (nullify) and modify a tag
	 * of an element.
	 *
	 * TODO-20111106: this is a legacy method, it will be removed as soon as possible.
	 */
	public function set($name, $value=null)
	{
		if (is_array($name))
		{
			foreach ($name as $tag => $value)
			{
				$this->set($tag, $value);
			}
		}
		else
		{
			$this[$name] = $value;
		}
	}

	/**
	 * The get() method is used to get to value of a tag. If the tag is not
	 * set, `null` is returned. You can provide a default value which is returned
	 * instead of `null` if the tag is not set.
	 *
	 * TODO-20111106: this is a legacy method, it will be removed as soon as possible.
	 */
	public function get($name, $default=null)
	{
		return isset($this->tags[$name]) ? $this->tags[$name] : $default;
	}

	/**
	 * @var array Class names used to compose the value of the "class" attribute.
	 */
	protected $class_names=array();

	/**
	 * Returns the value of the "class" attribute.
	 *
	 * @return string
	 */
	protected function __volatile_get_class()
	{
		return $this->render_class($this->class_names);
	}

	/**
	 * Sets the value of the "class" attribute.
	 *
	 * @param string $class
	 */
	protected function __volatile_set_class($class)
	{
		$names = explode(' ', trim($class));
		$names = array_map('trim', $names);

		$this->class_names = array_combine($names, array_fill(0, count($names), true));
	}

	/**
	 * Adds a class name to the "class" attribute.
	 *
	 * @param $class
	 */
	public function add_class($class)
	{
		$this->class_names[$class] = true;
	}

	/**
	 * Removes class name from the "class" attribute.
	 *
	 * @param $class
	 */
	public function remove_class($class)
	{
		unset($this->class_names[$class]);
	}

	/**
	 * Checks a class name in the class attribute.
	 *
	 * @param string $class_name
	 *
	 * @return boolean true if the element has the class name, false otherwise.
	 */
	public function has_class($class_name)
	{
		return isset($this->class_names[$class_name]);
	}

	/**
	 * Renders the `class` attribute value.
	 *
	 * @param array $class_names
	 *
	 * @return string
	 */
	protected function render_class(array $class_names)
	{
		return implode(' ', array_keys(array_filter($class_names)));
	}

	protected function handleValue(&$tags)
	{
		$value = $this['value'];

		if ($value === null)
		{
			$default = $this[self::DEFAULT_VALUE];

			if ($default)
			{
				if ($this->type == self::TYPE_CHECKBOX)
				{
					// TODO-20100108: we need to check this situation further more

					//$this->set('checked', $default);
				}
				else
				{
					$this['value'] = $default;
				}
			}
		}
	}

	/**
	 * Add a child to the element.
	 *
	 * @param $child The child element to add
	 *
	 * @param $name Optional, the name of the child element
	 */
	public function add_child($child, $name=null)
	{
		if ($name)
		{
			// TODO-20110926: I added the `&& empty($child->tags['name'])` part to avoid setting
			// the name twice, so the name defined is preserved, we need to check is this is
			// ok or not.

			if (is_object($child) && empty($child->tags['name']))
			{
				$child->set('name', $name);
			}

			$this->children[$name] = $child;
		}
		else
		{
			$this->children[] = $child;
		}
	}

	public function add_children(array $children)
	{
		foreach ($children as $name => $child)
		{
			$this->add_child($child, is_numeric($name) ? null : $name);
		}
	}

	/**
	 * Returns the children of the element ordered according to their weight.
	 *
	 * @return array[int]Element|string
	 */
	public function get_ordered_children()
	{
		if (!$this->children)
		{
			return array();
		}

		$by_weight = array();
		$with_relative_positions = array();

		foreach ($this->children as $name => $child)
		{
			$weight = is_object($child) ? $child[self::WEIGHT] : 0;

			if (is_string($weight) && !is_numeric($weight)) // FIXME: is is_numeric() not enought ?
			{
				$with_relative_positions[] = $child;

				continue;
			}

			$by_weight[(int) $weight][$name] = $child;
		}

		if (count($by_weight) == 1 && !$with_relative_positions)
		{
			return $this->children;
		}

		ksort($by_weight);

		$rc = array();

		foreach ($by_weight as $children)
		{
			$rc += $children;
		}

		#
		# now we deal with the relative positions
		#

		if ($with_relative_positions)
		{
			foreach ($with_relative_positions as $child)
			{
				list($target, $position) = explode(':', $child[self::WEIGHT]) + array(1 => 'after');

				$rc = array_insert($rc, $target, $child, $child['name'], $position == 'after');
			}
		}

		return $rc;
	}

	/*
	**

	CONTEXT

	**
	*/

	protected $pushed_tags = array();
	protected $pushed_children = array();
	protected $pushed_inner_html = array();

	public function contextPush()
	{
		array_push($this->pushed_tags, $this->tags);
		array_push($this->pushed_children, $this->children);
		array_push($this->pushed_inner_html, $this->inner_html);
	}

	public function contextPop()
	{
		$this->tags = array_pop($this->pushed_tags);
		$this->children = array_pop($this->pushed_children);
		$this->inner_html = array_pop($this->pushed_inner_html);
	}

	/**
	 * Returns the HTML representation of a child element.
	 *
	 * @param Element|string $child
	 *
	 * @return string
	 */
	protected function render_child($child)
	{
		return (string) $child;
	}

	/**
	 * Returns the HTML representation of the element's content.
	 *
	 * If the element has a null content it is considered as self closing.
	 *
	 * @return string The content of the element.
	 */
	protected function render_inner_html()
	{
		$rc = null;

		if ($this->type === 'select')
		{
			$rc = $this->render_inner_html_for_select();
		}

		$children = $this->get_ordered_children();

		if ($children)
		{
			foreach ($children as $child)
			{
				$rc .= $this->render_child($child);
			}
		}
		else if ($this->inner_html !== null)
		{
			$rc = $this->inner_html;
		}

		return $rc;
	}

	/**
	 * Renders inner HTML of SELECT elements.
	 *
	 * @return string
	 */
	protected function render_inner_html_for_select()
	{
		$this->contextPush();

		#
		# get the name and selected value for our children
		#

		$selected = $this['value'];

		#
		# this is the 'template' child
		#

		$option = new Element('option');

		#
		# create the inner content of our element
		#

		$rc = '';

		$options = $this[self::OPTIONS] ?: array();
		$disabled = $this[self::OPTIONS_DISABLED];

		foreach ($options as $value => $label)
		{
			#
			# value is casted to a string so that we can handle null value and compare '0' with 0
			#

			$option['value'] = $value;
			$option['selected'] = (string) $value === (string) $selected;
			$option['disabled'] = !empty($disabled[$value]);

			if ($label)
			{
				// TODO-20111106: only string prefixed with a dot "." were translated, this is only here for compat and should be removed as soon as possible.

				if ($label{0} == '.')
				{
					$label = substr($label, 1);
				}

				$label = t($label, array(), array('scope' => 'element.option'));
				$label = escape($label);
			}
			else
			{
				$label = '&nbsp;';
			}

			$option->inner_html = $label;

			$rc .= $option;
		}

		$this->contextPop();

		return $rc;
	}

	/**
	 * Renders dataset.
	 *
	 * The dataset is rendered as a series of "data-*" attributes. Values of type array are
	 * encoded using the {@link json_encode()} function. Attributes with null values are skipped.
	 *
	 * @param array $dataset
	 *
	 * @return string
	 */
	protected function render_dataset(array $dataset)
	{
		if (!$dataset)
		{
			return '';
		}

		$rc = '';

		foreach ($dataset as $name => $value)
		{
			if (is_array($value))
			{
				$value = json_encode($value);
			}

			if ($value === null)
			{
				continue;
			}

			$rc .= ' data-' . $name . '="' . (is_numeric($value) ? $value : escape($value)) . '"';
		}

		return $rc;
	}

	/**
	 * Returns the HTML representation of the element and its contents.
	 *
	 * The final element's attributes are filtered when the element is rendered. All custom
	 * attributes, those starting with the hash sign "#", are discarted. The `value`, `required`,
	 * `disabled` and `name` attributes are discarted if they are not supported by the element's
	 * type.
	 *
	 * If the element has a dataset each of its keys are mapped to a "data-+" attribute.
	 *
	 * Note: The inner HTML is rendered before the outer HTML in a try/catch block. If an
	 * exception is caught, it is converted into a string and used as inner HTML.
	 *
	 * If the inner HTML is null the element is self-closing.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		try
		{
			$inner = $this->render_inner_html();
		}
		catch (Exception\EmptyElement $e)
		{
			throw $e;
		}
		catch (\Exception $e)
		{
			$inner = render_exception($e);
		}

		#
		#
		#

		$rc = '<' . $this->tag_name;

		#
		# class
		#

		$class = $this->class;

		if ($class)
		{
			$rc .= ' class="' . $class . '"';
		}

		#
		# attributes
		#

		foreach ($this->tags as $attribute => $value)
		{
			#
			# We discard false, null or custom tags. The 'class' tag is also discarted because it's
			# handled separately.
			#

			if ($value === false || $value === null || $attribute{0} == '#' || $attribute == 'class')
			{
				continue;
			}

			#
			# The `value`, `required`, `disabled` and `name` attributes are discarted if they are
			# not supported by the element's type.
			#

			if ($attribute == 'value' && !in_array($this->tag_name, self::$has_attribute_value))
			{
				continue;
			}

			if ($attribute == 'required' && !in_array($this->tag_name, self::$has_attribute_required))
			{
				continue;
			}

			if ($attribute == 'disabled' && !in_array($this->tag_name, self::$has_attribute_disabled))
			{
				continue;
			}

			if ($attribute == 'name' && !in_array($this->tag_name, self::$inputs))
			{
				continue;
			}

			#
			# The 'title' attribute is translated within the 'element.title' scope.
			#

			if ($attribute == 'title')
			{
				// TODO-20111229: only string prefixed with a dot "." were translated, this is only here for compat and should be removed as soon as possible.

				if ($value{0} == '.')
				{
					$value = substr($value, 1);
				}

				$value = t($value, array(), array('scope' => 'element.title'));
			}

			#
			# attributes with the value TRUE are translated to XHTML standard
			# e.g. readonly="readonly"
			#

			if ($value === true)
			{
				$value = $attribute;
			}

			if (is_array($value))
			{
				throw new \InvalidArgumentException(format('Invalid value for attribute %attribute: :value', array('attribute' => $attribute, 'value' => $value)));
			}

			$rc .= ' ' . $attribute . '="' . (is_numeric($value) ? $value : escape($value)) . '"';
		}

		$dataset = $this->dataset;

		if (in_array($this->tag_name, self::$has_attribute_value) || $this->tag_name == 'textarea' && $this['data-default-value'] === null)
		{
			$dataset['default-value'] = $this[self::DEFAULT_VALUE];
		}

		$rc .= $this->render_dataset($dataset);

		#
		# if the inner HTML of the element is null, the element is self closing.
		#

		if ($inner === null)
		{
			$rc .= ' />';
		}
		else
		{
			$rc .= '>' . $inner . '</' . $this->tag_name . '>';
		}

		return $rc;
	}

	/**
	 * Returns a HTML representation of the complete element, including external labels and such.
	 *
	 * @return string
	 *
	 * @todo-20110813 The code from the __toString() method should be moved here, and the
	 * __toString() method should only call this method wrapped in a try/catch block.
	 *
	 * But before that, we need to modify how editors work because they already define a static
	 * "render" method used to render the data they are used to edit.
	 */
	/*
	protected function render()
	{

	}
	*/

	/**
	 * Decorate the specified HTML.
	 *
	 * If the label starts with a dot ".", the label is translated within the "element.label"
	 * scope (the dot is removed during the translation), otherwise the label is translated within
	 * the current scope.
	 *
	 * @param string $html
	 *
	 * @return string
	 */
	protected function decorate($html)
	{
		#
		# add label
		#

		$label = $this[self::LABEL];

		if ($label)
		{
			// TODO-20111106: only string prefixed with a dot "." were translated, this is only here for compat and should be removed as soon as possible.

			if ($label{0} == '.')
			{
				$label = substr($label, 1);
			}

			$label = t($label, array(), array('scope' => 'element.label'));
			$html = $this->decorate_with_label($html, $label);
		}

		#
		# add inline help
		#

		$help = $this[self::INLINE_HELP];

		if ($help)
		{
			// TODO-20111106: only string prefixed with a dot "." were translated, this is only here for compat and should be removed as soon as possible.

			if ($help{0} == '.')
			{
				$help = substr($help, 1);
			}

			$help = t($help, array(), array('scope' => 'element.inline_help'));
			$html = $this->decorate_with_inline_help($html, $help);
		}

		#
		# add description
		#

		$description = $this[self::DESCRIPTION];

		if ($description)
		{
			$description = t($description, array(), array('scope' => 'element.description'));
			$html = $this->decorate_with_description($html, $description);
		}

		#
		# add legend
		#

		$legend = $this[self::LEGEND];

		if ($legend)
		{
			$legend = t($legend, array(), array('scope' => 'element.legend'));
			$html = $this->decorate_with_legend($html, $legend);
		}

		return $html;
	}

	/**
	 * Decorates the specified HTML with specified label.
	 *
	 * The position of the label is defined using the{[@link T_LABEL_POSITION} tag. A separator is
	 * generaly append to the label, it can be removed by setting the {@link T_LABEL_SEPARATOR} tag
	 * to false.
	 *
	 * @param string $html
	 * @param string $label The label as defined by the {@link T_LABEL} tag.
	 *
	 * @return string
	 *
	 * @todo-20110813 use a translatable string for the separator e.g.
	 * 'brickrouge.label_with_separator' => ':label<span class="separator>:</span>'
	 *
	 * so that it can be tweaked e.g. in french
	 *
	 * 'brickrouge.label_with_separator' => ':label<span class="separator>&nbsp;:</span>'
	 */
	protected function decorate_with_label($html, $label)
	{
		$is_required = $this[self::REQUIRED];
		$position = $this->offsetGet(self::LABEL_POSITION, 'after');
		$separator = $this->offsetGet(self::LABEL_SEPARATOR, true);

		/*
		if ($is_required)
		{
			$label = $label . '<sup>&nbsp;*</sup>';
		}

		if ($position != 'after' && $separator)
		{
			$label .= '<span class="separator">&nbsp;:</span>';
		}
		*/

		//if ($position != 'above')
		{
			$label = '<span class="text">' . $label . '</span>';
		}

		// TODO-20100714: T_LABEL_SEPARATOR is no longer used, watch out for consequences

		$content = $html;
		$class = 'element-label';

		if ($is_required)
		{
			$class .= ' required';
		}

		switch ($position)
		{
			case 'above':
			{
				$html = <<<EOT
<label class="$class above">$label</label>
$content
EOT;
			}
			break;

			case 'before':
			{
				$html = <<<EOT
<label class="$class">$label $content</label>
EOT;
			}
			break;

			case 'after':
			default:
			{
				$html = <<<EOT
<label class="$class">$content $label</label>
EOT;
			}
			break;
		}

		return $html;
	}

	/**
	 * Decorates the specified HTML with a fieldset and the specified legend.
	 *
	 * @param string $html
	 * @param string $legend
	 *
	 * @return string
	 */
	protected function decorate_with_legend($html, $legend)
	{
		return '<fieldset><legend>' . $legend . '</legend>' . $html . '</fieldset>';
	}

	/**
	 * Decorates the specified HTML with an inline help element.
	 *
	 * @param string $html
	 * @param string $help
	 *
	 * @return string
	 */
	protected function decorate_with_inline_help($html, $help)
	{
		return $html . '<span class="help-inline">' . $help . '</span>';
	}

	/**
	 * Decoartes the specified HTML with the specified description.
	 *
	 * @param string $html
	 * @param string $description
	 *
	 * @return string
	 */
	protected function decorate_with_description($html, $description)
	{
		return $html . '<div class="element-description help-block">' . $description . '</div>';
	}

	private static $assets_handled=array();

	protected static function handle_assets()
	{
		$class = get_called_class();

		if (isset(self::$assets_handled[$class]))
		{
			return;
		}

		self::$assets_handled[$class] = true;

		call_user_func($class . '::add_assets', get_document());
	}

	/**
	 * Adds assets to the document.
	 *
	 * @param Document $document
	 */
	protected static function add_assets(Document $document)
	{

	}

	/**
	 * Returns the HTML representation of the element, including external labels and such.
	 *
	 * @return string The HTML representation of the object
	 *
	 * @todo-20110813 The method shoul call a 'decorate' method to add external labels and such to
	 * the element.
	 */
	public function __toString()
	{
		if (get_class($this) != __CLASS__)
		{
			static::handle_assets();
		}

		$rc = '';

		$tags =& $this->tags;

		#
		# handle value for some selected 'types' and 'elements'
		#

		static $valued_elements = array
		(
			'input', 'select', 'button', 'textarea'
		);

		if (in_array($this->tag_name, $valued_elements))
		{
			$this->handleValue($tags);
		}

		#
		#
		#

		switch ($this->type)
		{
			case self::TYPE_CHECKBOX:
			{
				$this->contextPush();

				if ($this[self::DEFAULT_VALUE] && $this['checked'] === null)
				{
					$this['checked'] = true;
				}

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case self::TYPE_CHECKBOX_GROUP:
			{
				$this->contextPush();

				$this->handleValue($tags);

				#
				# get the name and selected value for our children
				#

				$name = $this['name'];
				$selected = $this['value'] ?: array();
				$disabled = $this['disabled'] ?: false;
				$readonly = $this['readonly'] ?: false;

				#
				# this is the 'template' child
				#

				$child = new Element
				(
					'input', array
					(
						'type' => 'checkbox',
						'readonly' => $readonly
					)
				);

				#
				# create the inner content of our element
				#

				$inner = null;
				$disableds = $this[self::OPTIONS_DISABLED];

				foreach ($tags[self::OPTIONS] as $option_name => $label)
				{
					$child[self::LABEL] = $label;
					$child['name'] = $name . '[' . $option_name . ']';
					$child['checked'] = !empty($selected[$option_name]);
					$child['disabled'] = $disabled || !empty($disableds[$option_name]);

					$inner .= $child;
				}

				$this->inner_html .= $inner;

				#
				# make our element
				#

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case self::TYPE_FILE:
			{
				$rc .= '<div class="wd-file">';

				#
				# the FILE_WITH_REMINDER tag can be used to add a disabled text input before
				# the file element. this text input is used to display the current value of the
				# file element.
				#

				$reminder = $this[self::FILE_WITH_REMINDER];

				if ($reminder === true)
				{
					$reminder = $this['value'];
				}

				if ($reminder)
				{
					$rc .= '<div class="reminder">';

					$rc .= new Text
					(
						array
						(
							'value' => $reminder,
							'disabled' => true,
							'size' => $this['size'] ?: 40
						)
					);

					$rc .= ' ';

					$rc .= new A
					(
						'Download', $reminder, array
						(
							'title' => $reminder,
							'target' => '_blank'
						)
					);

					$rc .= '</div>';
				}
				#
				#
				#

				$rc .= $this->render_outer_html();

				#
				# the FILE_WITH_LIMIT tag can be used to add a little text after the element
				# reminding the maximum file size allowed for the upload
				#

				$limit = $this->get(self::FILE_WITH_LIMIT);

				if ($limit)
				{
					if ($limit === true)
					{
						$limit = ini_get('upload_max_filesize') * 1024;
					}

					$limit = format_size($limit * 1024);

					$rc .= PHP_EOL;
					$rc .= '<div class="limit">';
					$rc .= t('The maximum file size must be less than :size.', array(':size' => $limit));
					$rc .= '</div>';
				}

				$rc .= '</div>';
			}
			break;

			case self::TYPE_RADIO_GROUP:
			{
				$this->contextPush();

				$this->handleValue($tags);

				#
				# get the name and selected value for our children
				#

				$name = $this['name'];
				$selected = $this['value'];
				$disabled = $this['disabled'] ?: false;
				$readonly = $this['readonly'] ?: false;

				#
				# this is the 'template' child
				#

				$child = new Element
				(
					'input', array
					(
						'type' => 'radio',
						'name' => $name,
						'readonly' => $readonly
					)
				);

				#
				# create the inner content of our element
				#
				# add our options as children
				#

				$disableds = $this->get(self::OPTIONS_DISABLED);

				foreach ($tags[self::OPTIONS] as $value => $label)
				{
					if ($label && $label{0} == '.')
					{
						$label = t(substr($label, 1), array(), array('scope' => 'element.option'));
					}

					$child[self::LABEL] = $label;
					$child['value'] = $value;
					$child['checked'] = (string) $value === (string) $selected;
					$child['disabled'] = $disabled || !empty($disableds[$value]);

					$this->inner_html .= $child;
				}

				#
				# make our element
				#

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case 'textarea':
			{
				$this->contextPush();

				$this->inner_html = escape($this['value'] ?: '');

				$this->set('value', null);

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			default:
			{
				try
				{
					$rc = $this->render_outer_html();
				}
				catch (Exception\EmptyElement $e)
				{
					return '';
				}
				catch (\Exception $e)
				{
					$rc = render_exception($e);
				}
			}
			break;
		}

		return $this->decorate($rc);
	}

	/**
	 * Validates the value of the element.
	 *
	 * This function uses the validator defined using the VALIDATOR tag to validate
	 * its value.
	 *
	 * @param $value
	 *
	 * @return boolean Return TRUE is the validation succeed.
	 */
	public function validate($value, Errors $errors)
	{
		$validator = $this[self::VALIDATOR];
		$options = $this[self::VALIDATOR_OPTIONS];

		if ($validator)
		{
			list($callback, $params) = $validator + array(1 => array());

			return call_user_func($callback, $errors, $this, $value, $params);
		}

		#
		# default validator
		#

		if (!$options)
		{
			return true;
		}

		switch ($this->type)
		{
			case self::TYPE_CHECKBOX_GROUP:
			{
				if (isset($options['max-checked']))
				{
					$limit = $options['max-checked'];

					if (count($value) > $limit)
					{
						$errors[$this->name] = t('Le nombre de choix possible pour le champ %name est limité à :limit', array
						(
							'name' => Form::select_element_label($this),
							'limit' => $limit
						));

						return false;
					}
				}
			}
			break;
		}

		return true;
	}
}