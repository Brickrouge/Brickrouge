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
 * An HTML element.
 *
 * The `Element` class can create any kind of HTML element. It supports class names, dataset,
 * children. It handles values and default values. It can decorate the HTML element with a label,
 * a legend and a description.
 *
 * This is the base class to all element types.
 *
 * @property string $class Assigns a class name or set of class names to an element. Any number of
 * elements may be assigned the same class name or names. Multiple class names must be separated
 * by white space characters.
 *
 * @property Dataset $dataset The dataset property provides a convenient mapping to the
 * data-* attributes on an element.
 *
 * @property string $id Assigns an identifier to an element. This identifier mush be unique in
 * a document.
 *
 * @see http://dev.w3.org/html5/spec/Overview.html#embedding-custom-non-visible-data-with-the-data-attributes
 */
class Element extends \ICanBoogie\Object implements \ArrayAccess, \IteratorAggregate
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
	const LABEL = '#label';

	/**
	 * Used to define the position of the label. Possible positions are "before", "after" and
	 * "above". Defaults to "after".
	 *
	 * @var string
	 */
	const LABEL_POSITION = '#label-position';
	const LABEL_MISSING = '#label-missing';

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
	 * Used to define the options of the following element types: "select",
	 * {@link TYPE_RADIO_GROUP} and {@link TYPE_CHECKBOX_GROUP}.
	 *
	 * @var string
	 */
	const OPTIONS = '#options';

	/**
	 * Used to define which options are disabled.
	 *
	 * @var string
	 */
	const OPTIONS_DISABLED = '#options-disabled';

	/**
	 * Used to define the state of the element: "success", "warning" or "error".
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

	/**
	 * The name of the Javascript constructor that should be used to construct the widget.
	 *
	 * @var string
	 */
	const WIDGET_CONSTRUCTOR = '#widget-constructor';

	static private $inputs = array('button', 'form', 'input', 'option', 'select', 'textarea');
	static private $has_attribute_disabled = array('button', 'input', 'optgroup', 'option', 'select', 'textarea');
	static private $has_attribute_value = array('button', 'input', 'option');
	static private $has_attribute_required = array('input', 'select', 'textarea');
	static private $handled_assets = array();

	static protected function handle_assets()
	{
		$class = get_called_class();

		if (isset(self::$handled_assets[$class]))
		{
			return;
		}

		self::$handled_assets[$class] = true;

		static::add_assets(get_document());
	}

	/**
	 * Adds assets to the document.
	 *
	 * @param Document $document
	 */
	static protected function add_assets(Document $document)
	{

	}

	/**
	 * Next available auto element id index.
	 *
	 * @var int
	 */
	static protected $auto_element_id = 1;

	/**
	 * Returns a unique element id string.
	 *
	 * @return string
	 */
	static public function auto_element_id()
	{
		return 'autoid--' . self::$auto_element_id++;
	}

	/**
	 * Type if the element, as provided during {@link __construct()}.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Tag name of the rendered HTML element.
	 *
	 * @var string
	 */
	public $tag_name;

	/**
	 * An array containing the children of the element.
	 *
	 * @var array
	 */
	public $children = array();

	/**
	 * Tags of the element, including HTML and special attributes.
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
	 * @param string $type Type of the element, it can be one of the custom types (`TYPE_*`) or
	 * any HTML type.
	 *
	 * @param array $attributes HTML and custom attributes.
	 */
	public function __construct($type, $attributes=array())
	{
		$this->type = $type;

		#
		# children first
		#

		if (!empty($attributes[self::CHILDREN]))
		{
			$this->children = array();
			$this->adopt($attributes[self::CHILDREN]);

			unset($attributes[self::CHILDREN]);
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
				$attributes['type'] = $translate[$type][1];
			}
			break;

			case self::TYPE_CHECKBOX_GROUP:
			{
				$this->tag_name = 'div';
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

				$attributes += array('rows' => 10, 'cols' => 76);
			}
			break;

			default:
			{
				$this->tag_name = $type;
			}
			break;
		}

		foreach ($attributes as $attribute => $value)
		{
			$this[$attribute] = $value;
		}

		switch ((string) $this->type)
		{
			case self::TYPE_CHECKBOX_GROUP: $this->add_class('checkbox-group'); break;
			case self::TYPE_RADIO_GROUP: $this->add_class('radio-group'); break;
		}
	}

	/**
	 * Checks is an attribute is set.
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function offsetExists($attribute)
	{
		return isset($this->tags[$attribute]);
	}

	/**
	 * Returns the value of an attribute.
	 *
	 * @param string $attribute
	 * @param null $default The default value of the attribute.
	 *
	 * @return mixed|null The value of the attribute, or null if is not set.
	 */
	public function offsetGet($attribute, $default=null)
	{
		return isset($this->tags[$attribute]) ? $this->tags[$attribute] : $default;
	}

	/**
	 * Sets the value of an attribute.
	 *
	 * @param string $attribute The attribute.
	 * @param mixed $value The value of the attribute.
	 */
	public function offsetSet($attribute, $value)
	{
		switch ($attribute)
		{
			case self::CHILDREN:
			{
				$this->children = array();
				$this->adopt($value);
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

		$this->tags[$attribute] = $value;
	}

	/**
	 * Removes an attribute.
	 *
	 * @param string $attribute The name of the attribute.
	 */
	public function offsetUnset($attribute)
	{
		unset($this->tags[$attribute]);
	}

	/**
	 * Returns an iterator.
	 *
	 * @return Iterator
	 */
	public function getIterator()
	{
		return new Iterator($this);
	}

	/**
	 * Returns the {@link Dataset} of the element.
	 *
	 * @return Dataset
	 */
	protected function get_dataset()
	{
		return new Dataset($this);
	}

	/**
	 * Sets the datset of the element.
	 *
	 * @param array|Dataset $properties
	 *
	 * @return Dataset
	 */
	protected function set_dataset($properties)
	{
		if ($properties instanceof Dataset)
		{
			return $properties;
		}

		return new Dataset($this, $properties);
	}

	protected function volatile_get_attributes()
	{
		return $this->tags;
	}

	/**
	 * Returns the element's id.
	 *
	 * If the element's id is empty, a unique id is generated and added to its tags.
	 *
	 * @return string
	 */
	protected function get_id()
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
	 * Class names used to compose the value of the `class` attribute.
	 *
	 * @var array
	 */
	protected $class_names = array();

	/**
	 * Returns the value of the "class" attribute.
	 *
	 * @return string
	 */
	protected function volatile_get_class()
	{
		$class_names = $this->alter_class_names($this->class_names);

		return $this->render_class($class_names);
	}

	/**
	 * Sets the value of the "class" attribute.
	 *
	 * @param string $class
	 */
	protected function volatile_set_class($class)
	{
		$names = explode(' ', trim($class));
		$names = array_map('trim', $names);

		$this->class_names = array_combine($names, array_fill(0, count($names), true));
	}

	/**
	 * Adds a class name to the "class" attribute.
	 *
	 * @param $class
	 *
	 * @return Element
	 */
	public function add_class($class)
	{
		$this->class_names[$class] = true;

		return $this;
	}

	/**
	 * Removes a class name from the `class` attribute.
	 *
	 * @param $class
	 *
	 * @return Element
	 */
	public function remove_class($class)
	{
		unset($this->class_names[$class]);

		return $this;
	}

	/**
	 * Checks if a class name is defined in the `class` attribute.
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
	 * Alters the class names.
	 *
	 * This method is invoked before the class names are rendered.
	 *
	 * @param array $class_names
	 *
	 * @return array
	 */
	protected function alter_class_names(array $class_names)
	{
		return $class_names;
	}

	/**
	 * Renders the `class` attribute value.
	 *
	 * @param array $class_names An array of class names. Each key/value pair describe a class
	 * name. The key is the identifier of the class name, the value is its value. If the value is
	 * empty then the class name is discarted. If the value is `true` the identifier of the class
	 * name is used as value.
	 *
	 * @return string
	 */
	protected function render_class(array $class_names)
	{
		$class = '';
		$class_names = array_filter($class_names);

		foreach ($class_names as $name => $value)
		{
			if ($value === true)
			{
				$value = $name;
			}

			$class .= ' ' . $value;
		}

		return substr($class, 1);
	}

	/**
	 * Add a child or children to the element.
	 *
	 * If the children are provided in an array, each key/value pair defines the name of a child
	 * and the child itself. If the key is not numeric it is considered as the child's name and is
	 * used to set its `name` attribute, unless the attribute is already defined.
	 *
	 * @param string|Element|array $child The child or children to add.
	 * @param string|Element $other[optional] Other child.
	 */
	public function adopt($child, $other=null)
	{
		if (func_num_args() > 1)
		{
			$child = func_get_args();
		}

		if (is_array($child))
		{
			$children = $child;

			foreach($children as $name => $child)
			{
				if (is_numeric($name))
				{
					$this->children[] = $child;
				}
				else
				{
					if ($child instanceof self && $child['name'] === null)
					{
						$child['name'] = $name;
					}

					$this->children[$name] = $child;
				}
			}
		}
		else
		{
			$this->children[] = $child;
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

			if (is_string($weight) && !is_numeric($weight)) // FIXME: is is_numeric() not enough ?
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
	 * Renders the children of the element into a HTML string.
	 *
	 * @param array $children
	 *
	 * @return string
	 */
	protected function render_children(array $children)
	{
		$html = '';

		foreach ($children as $child)
		{
			$html .= $this->render_child($child);
		}

		return $html;
	}

	/**
	 * Returns the HTML representation of the element's content.
	 *
	 * The children of the element are ordered before they are rendered using the
	 * {@link render_children()} method.
	 *
	 * According to their types, the following methods can be invoked to render the inner HTML
	 * of elements:
	 *
	 * - {@link render_inner_html_for_select}
	 * - {@link render_inner_html_for_textarea}
	 * - {@link render_inner_html_for_checkbox_group}
	 * - {@link render_inner_html_for_radio_group}
	 *
	 * @return string|null The content of the element. The element is to be considered
	 * _self-closing_ if `null` is returned.
	 */
	protected function render_inner_html()
	{
		$html = null;

		switch ($this->type)
		{
			case 'select': $html = $this->render_inner_html_for_select(); break;
			case 'textarea': $html = $this->render_inner_html_for_textarea(); break;
			case self::TYPE_CHECKBOX_GROUP: $html = $this->render_inner_html_for_checkbox_group(); break;
			case self::TYPE_RADIO_GROUP: $html = $this->render_inner_html_for_radio_group(); break;
		}

		$children = $this->get_ordered_children();

		if ($children)
		{
			$html .= $this->render_children($children);
		}
		else if ($this->inner_html !== null)
		{
			$html = $this->inner_html;
		}

		return $html;
	}

	/**
	 * Renders inner HTML of `SELECT` elements.
	 *
	 * @return string
	 */
	protected function render_inner_html_for_select()
	{
		#
		# get the name and selected value for our children
		#

		$selected = $this['value'];

		if ($selected === null)
		{
			$selected = $this[self::DEFAULT_VALUE];
		}

		#
		# this is the 'template' child
		#

		$dummy_option = new Element('option');

		#
		# create the inner content of our element
		#

		$html = '';

		$options = $this[self::OPTIONS] ?: array();
		$disabled = $this[self::OPTIONS_DISABLED];

		foreach ($options as $value => $label)
		{
			if ($label instanceof self)
			{
				$option = $label;
			}
			else
			{
				$option = $dummy_option;

				if ($label || is_numeric($label))
				{
					$label = escape(t($label, array(), array('scope' => 'element.option')));
				}
				else
				{
					$label = '&nbsp;';
				}

				$option->inner_html = $label;
			}

			#
			# value is casted to a string so that we can handle null value and compare '0' with 0
			#

			$option['value'] = $value;
			$option['selected'] = (string) $value === (string) $selected;
			$option['disabled'] = !empty($disabled[$value]);

			$html .= $option;
		}

		return $html;
	}

	/**
	 * Renders the inner HTML of `TEXTAREA` elements.
	 *
	 * @return string
	 */
	protected function render_inner_html_for_textarea()
	{
		$value = $this['value'];

		if ($value === null)
		{
			$value = $this[self::DEFAULT_VALUE];
		}

		return escape($value);
	}

	/**
	 * Renders inner HTML of {@link TYPE_CHECKBOX_GROUP} custom elements.
	 *
	 * @return string
	 */
	protected function render_inner_html_for_checkbox_group()
	{
		#
		# get the name and selected value for our children
		#

		$name = $this['name'];
		$selected = (array) $this['value'] ?: $this[self::DEFAULT_VALUE];
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

		$html = '';
		$disableds = $this[self::OPTIONS_DISABLED];

		foreach ($this[self::OPTIONS] as $option_name => $label)
		{
			$child[self::LABEL] = $label;
			$child['name'] = $name . '[' . $option_name . ']';
			$child['checked'] = !empty($selected[$option_name]);
			$child['disabled'] = $disabled || !empty($disableds[$option_name]);
			$child['data-key'] = $option_name;

			$html .= $child;
		}

		return $html;
	}

	/**
	 * Renders inner HTML of {@link TYPE_RADIO_GROUP} custom elements.
	 *
	 * @return string
	 */
	protected function render_inner_html_for_radio_group()
	{
		#
		# get the name and selected value for our children
		#

		$name = $this['name'];
		$selected = $this['value'];

		if ($selected === null)
		{
			$selected = $this[self::DEFAULT_VALUE];
		}

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

		$html = '';
		$disableds = $this[self::OPTIONS_DISABLED];

		foreach ($this[self::OPTIONS] as $value => $label)
		{
			if ($label && !is_object($label) && $label{0} == '.')
			{
				$label = t(substr($label, 1), array(), array('scope' => 'element.option'));
			}

			$child[self::LABEL] = $label;
			$child['value'] = $value;
			$child['checked'] = (string) $value === (string) $selected;
			$child['disabled'] = $disabled || !empty($disableds[$value]);

			$html .= $child;
		}

		return $html;
	}

	/**
	 * Alters the provided attributes.
	 *
	 * - The `value`, `required`, `disabled` and `name` attributes are discarded if they are not
	 * supported by the element type.
	 *
	 * - The `title` attribute is translated within the scope `element.title`.
	 *
	 * - The `checked` attribute of elements of type {@link TYPE_CHECKBOX} is set to `true` if
	 * their {@link DEFAULT_VALUE} attribute is not empty and their `checked` attribute is not
	 * defined (`null`).
	 *
	 * - The `value` attribute of `INPUT` and `BUTTON` elements is altered if the
	 * {@link DEFAULT_VALUE} attribute is defined and the `value` attribute is not (`null`).
	 *
	 * @param array $attributes
	 *
	 * @return array The altered attributes.
	 */
	protected function alter_attributes(array $attributes)
	{
		$tag_name = $this->tag_name;

		foreach ($attributes as $attribute => $value)
		{
			if ($attribute == 'value' && !in_array($tag_name, self::$has_attribute_value))
			{
				unset($attributes[$attribute]);

				continue;
			}

			if ($attribute == 'required' && !in_array($tag_name, self::$has_attribute_required))
			{
				unset($attributes[$attribute]);

				continue;
			}

			if ($attribute == 'disabled' && !in_array($tag_name, self::$has_attribute_disabled))
			{
				unset($attributes[$attribute]);

				continue;
			}

			if ($attribute == 'name' && !in_array($tag_name, self::$inputs))
			{
				unset($attributes[$attribute]);

				continue;
			}

			if ($attribute == 'title')
			{
				$attributes[$attribute] = t($value, array(), array('scope' => 'element.title'));
			}
		}

		#
		# value/checked
		#

		if ($this->type == self::TYPE_CHECKBOX && $this['checked'] === null)
		{
			$attributes['checked'] = !!$this[self::DEFAULT_VALUE];
		}
		else if (($tag_name == 'input' || $tag_name == 'button') && $this['value'] === null)
		{
			$attributes['value'] = $this[self::DEFAULT_VALUE];
		}

		return $attributes;
	}

	/**
	 * Renders attributes.
	 *
	 * Attributes with `false` or `null` values as well as custom attributes are discarted.
	 * Attributes with the `true` value are translated to XHTML standard e.g. readonly="readonly".
	 *
	 * @param array $attributes
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException if the value is an array or an object that doesn't
	 * implement the `toString()` method.
	 */
	protected function render_attributes(array $attributes)
	{
		$html = '';

		foreach ($attributes as $attribute => $value)
		{
			if ($value === false || $value === null || $attribute{0} == '#')
			{
				continue;
			}
			else if ($value === true)
			{
				$value = $attribute;
			}
			else if (is_array($value) || (is_object($value) && !method_exists($value, '__toString')))
			{
				throw new \InvalidArgumentException(format('Invalid value for attribute %attribute: :value', array('attribute' => $attribute, 'value' => $value)));
			}

			$html .= ' ' . $attribute . '="' . (is_numeric($value) ? $value : escape($value)) . '"';
		}

		return $html;
	}

	/**
	 * Alters the dataset.
	 *
	 * The method is invoked before the dataset is rendered.
	 *
	 * The method might add the `default-value` and `widget-constructor` keys.
	 *
	 * @param array $dataset
	 *
	 * @return array
	 */
	protected function alter_dataset(array $dataset)
	{
		if ((in_array($this->tag_name, self::$has_attribute_value) || $this->tag_name == 'textarea')
		&& $this['data-default-value'] === null)
		{
			$dataset['default-value'] = $this[self::DEFAULT_VALUE];
		}

		if (!isset($dataset['widget-constructor']))
		{
			$dataset['widget-constructor'] = $this[self::WIDGET_CONSTRUCTOR];
		}

		return $dataset;
	}

	/**
	 * Renders dataset.
	 *
	 * The dataset is rendered as a series of "data-*" attributes. Values of type array are
	 * encoded using the {@link json_encode()} function. Attributes with null values are discarted,
	 * but unlike classic attributes boolean values are converted to integers.
	 *
	 * @param array $dataset
	 *
	 * @return string
	 */
	protected function render_dataset(array $dataset)
	{
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
			else if ($value === false)
			{
				$value = 0;
			}

			$rc .= ' data-' . $name . '="' . (is_numeric($value) ? $value : escape($value)) . '"';
		}

		return $rc;
	}

	/**
	 * Returns the HTML representation of the element and its contents.
	 *
	 * The attributes are filtered before they are rendered. The attributes with a `false` or
	 * `null` value are discarded as well as custom attributes, attributes that start with the has
	 * sign "#". The dataset properties—attributes starting with "data-*"—are extracted to be
	 * handled separately.
	 *
	 * The {@link alter_attributes()} method is invoked to alter the attributes and the
	 * {@link render_attributes()} method is invoked to render them.
	 *
	 * The {@link alter_dataset()} method is invoked to alter the dataset and the
	 * {@link render_dataset()} method is invoked to render them.
	 *
	 * If the inner HTML is null the element is self-closing.
	 *
	 * Note: The inner HTML is rendered before the outer HTML.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		$inner = $this->render_inner_html();
		$attributes = array();
		$dataset = array();

		foreach ($this->tags as $attribute => $value)
		{
			if (strpos($attribute, 'data-') === 0)
			{
				$dataset[substr($attribute, 5)] = $value;
			}
			else
			{
				$attributes[$attribute] = $value;
			}
		}

		$class = $this->class;

		if ($class)
		{
			$attributes['class'] = $class;
		}

		$html = '<'
		. $this->tag_name
		. $this->render_attributes($this->alter_attributes($attributes))
		. $this->render_dataset($this->alter_dataset($dataset));

		#
		# if the inner HTML of the element is `null`, the element is self closing.
		#

		if ($inner === null)
		{
			$html .= ' />';
		}
		else
		{
			$html .= '>' . $inner . '</' . $this->tag_name . '>';
		}

		return $html;
	}

	/**
	 * Decorates the specified HTML.
	 *
	 * The HTML can be decorated by following attributes:
	 *
	 * - A label defined by the {@link LABEL} special attribute. The {@link decorate_with_label()}
	 * method is used to decorate the HTML with the label.
	 *
	 * - An inline help defined by the {@link INLINE_HELP} special attribute. The
	 * {@link decorate_with_inline_help()} method is used to decorate the HTML with the inline
	 * help.
	 *
	 * - A description (or help block) defined by the {@link DESCRIPTION} special attribute. The
	 * {@link decorate_with_description()} method is used to decorate the HTML with the
	 * description.
	 *
	 * - A legend defined by the {@link LEGEND} special attribute. The
	 * {@link decorate_with_label()} method is used to decorate the HTML with the legend.
	 *
	 * @param string $html The HTML to decorate.
	 *
	 * @return string The decorated HTML.
	 */
	protected function decorate($html)
	{
		#
		# add label
		#

		$label = $this[self::LABEL];

		if ($label)
		{
			$label = t($label, array(), array('scope' => 'element.label'));
			$html = $this->decorate_with_label($html, $label);
		}

		#
		# add inline help
		#

		$help = $this[self::INLINE_HELP];

		if ($help)
		{
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
	 * The position of the label is defined using the {@link T_LABEL_POSITION} tag
	 *
	 * @param string $html
	 * @param string $label The label as defined by the {@link T_LABEL} tag.
	 *
	 * @return string
	 */
	protected function decorate_with_label($html, $label)
	{
		$class = 'element-label';

		if ($this[self::REQUIRED])
		{
			$class .= ' required';
		}

		switch ($this[self::LABEL_POSITION] ?: 'after')
		{
			case 'above': return <<<EOT
<label class="$class above">$label</label>
$html
EOT;

			case 'below': return <<<EOT
$html
<label class="$class below">$label</label>
EOT;

			case 'before': return <<<EOT
<label class="$class wrapping before"><span class="label-text">$label</span> $html</label>
EOT;

			case 'after':
			default: return <<<EOT
<label class="$class wrapping after">$html <span class="label-text">$label</span></label>
EOT;
		}
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
	 * Decorates the specified HTML with the specified description.
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

	/**
	 * Renders the element into an HTML string.
	 *
	 * Before the element is rendered the method  {@link handle_assets()} is invoked.
	 *
	 * The inner HTML is rendered by the {@link render_inner_html()} method. The outer HTML is
	 * rendered by the {@link render_outer_html()} method. Finaly, the HTML is decorated by
	 * the {@link decorate()} method.
	 *
	 * If the {@link ElementIsEmpty} exception is caught during the rendering
	 * an empty string is returned.
	 *
	 * @return string The HTML representation of the object
	 */
	public function render()
	{
		if (get_class($this) != __CLASS__)
		{
			static::handle_assets();
		}

		try
		{
			$html = $this->render_outer_html();

			return $this->decorate($html);
		}
		catch (ElementIsEmpty $e)
		{
			return '';
		}
	}

	/**
	 * Renders the element into an HTML string.
	 *
	 * The method {@link render()} is invoked to render the element.
	 *
	 * If an exception is thrown during the rendering it is rendered using the
	 * {@link render_exception()} function and returned.
	 *
	 * @return string The HTML representation of the object
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			return render_exception($e);
		}
	}

	/**
	 * Validates the specified value.
	 *
	 * This function uses the validator defined using the {@link VALIDATOR} special attribute to
	 * validate its value.
	 *
	 * @param $value
	 * @param \ICanBoogie\Errors $errors
	 *
	 * @return boolean `true` if  the validation succeed, `false` otherwise.
	 */
	public function validate($value, \ICanBoogie\Errors $errors)
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

/**
 * Exception thrown when one wants to cancel the whole rendering of an empty element. The
 * {@link Element} class takes care of this special case and instead of rendering the exception
 * only returns an empty string as the result of its {@link Element::render()} method.
 */
class ElementIsEmpty extends \Exception
{
	public function __construct($message="The element is empty.", $code=200, \Exception $previous=null)
	{
		parent::__construct($message, $code, $previous);
	}
}