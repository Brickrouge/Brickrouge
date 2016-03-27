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

use ICanBoogie\Prototyped;

/**
 * An HTML element.
 *
 * The `Element` class can create any kind of HTML element. It supports class names, dataset,
 * children. It handles values and default values. It can decorate the HTML element with a label,
 * a legend and a description.
 *
 * This is the base class to all element types.
 *
 * @property-read array $attributes Element's attributes.
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
 * @property string $name Assigned by {@link Form} during validation.
 *
 * @property-read array $ordered_children Children ordered according to their {@link WEIGHT}.
 *
 *
 * @see http://dev.w3.org/html5/spec/Overview.html#embedding-custom-non-visible-data-with-the-data-attributes
 */
class Element extends Prototyped implements \ArrayAccess, \IteratorAggregate, HTMLStringInterface
{
	#
	# special elements
	#

	/**
	 * Custom type used to create checkbox elements.
	 */
	const TYPE_CHECKBOX = '#checkbox';

	/**
	 * Custom type used to create checkbox group elements.
	 */
	const TYPE_CHECKBOX_GROUP = '#checkbox-group';

	/**
	 * Custom type used to create radio elements.
	 */
	const TYPE_RADIO = '#radio';

	/**
	 * Custom type used to create radio group elements.
	 */
	const TYPE_RADIO_GROUP = '#radio-group';

	#
	# special attributes
	#

	/**
	 * Defines the children of an element.
	 */
	const CHILDREN = '#children';

	/**
	 * Defines the default value of an element.
	 *
	 * The default value is added to the dataset as 'default-value'.
	 */
	const DEFAULT_VALUE = '#default-value';

	/**
	 * Defines the description block of an element.
	 *
	 * @see Element::decorate_with_description()
	 */
	const DESCRIPTION = '#description';

	/**
	 * Defines the group of an element.
	 */
	const GROUP = '#group';

	/**
	 * Defines the groups that can be used by children elements.
	 */
	const GROUPS = '#groups';

	/**
	 * Defines the inline help of an element.
	 *
	 * @see Element::decorate_with_inline_help()
	 */
	const INLINE_HELP = '#inline-help';

	/**
	 * Defines the inner HTML of an element. If the value of the tag is null, the markup will be
	 * self-closing.
	 */
	const INNER_HTML = '#inner-html';

	/**
	 * Defines the label of an element.
	 *
	 * @see Element::decorate_with_label()
	 */
	const LABEL = '#label';

	/**
	 * Defines the position of the label. Defaults to {@link LABEL_POSITION_AFTER}.
	 */
	const LABEL_POSITION = '#label-position';
	const LABEL_POSITION_BEFORE = 'before';
	const LABEL_POSITION_AFTER = 'after';
	const LABEL_POSITION_ABOVE = 'above';

	/**
	 * Defines the label to use to format validation message.
	 */
	const LABEL_MISSING = '#label-missing';

	/**
	 * Defines the legend of an element. If the legend is defined the element is wrapped
	 * into a fieldset when it is rendered.
	 *
	 * @see Element::decorate_with_legend()
	 */
	const LEGEND = '#element-legend';

	/**
	 * Defines the required state of an element.
	 *
	 * @see Form::validate()
	 * @see http://dev.w3.org/html5/spec/Overview.html#the-required-attribute
	 */
	const REQUIRED = 'required';

	/**
	 * Defines the options of the following element types: "select", {@link TYPE_RADIO_GROUP}
	 * and {@link TYPE_CHECKBOX_GROUP}.
	 */
	const OPTIONS = '#options';

	/**
	 * Defines which options are disabled.
	 */
	const OPTIONS_DISABLED = '#options-disabled';

	/**
	 * Defines the state of the element.
	 */
	const STATE = '#state';
	const STATE_DEFAULT = null;
	const STATE_DANGER = 'danger';
	const STATE_SUCCESS = 'success';
	const STATE_WARNING = 'warning';

	/**
	 * Defines the translator used to translate strings.
	 */
	const TRANSLATOR = '#translator';

	/**
	 * Defines validation rules.
	 */
	const VALIDATION = '#validation';

	/**
	 * Defines the weight of an element. This attribute can be used to reorder children when
	 * a parent element is rendered.
	 *
	 * @see Element::get_ordered_children()
	 */
	const WEIGHT = '#weight';
	const WEIGHT_BEFORE_PREFIX = 'before:';
	const WEIGHT_AFTER_PREFIX = 'after:';

	/**
	 * Defines the factory name for the widget.
	 */
	const IS = 'brickrouge-is';

	static private $inputs = [ 'button', 'form', 'input', 'option', 'select', 'textarea' ];
	static private $has_attribute_disabled = [ 'button', 'input', 'optgroup', 'option', 'select', 'textarea' ];
	static private $has_attribute_value = [ 'button', 'input', 'option' ];
	static private $has_attribute_required = [ 'input', 'select', 'textarea' ];
	static private $handled_assets = [];

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
	public $children = [];

	/**
	 * Attributes of the element, including HTML and special attributes.
	 *
	 * @var array[string]mixed
	 */
	private $attributes = [];

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
	public function __construct($type, array $attributes = [])
	{
		$this->type = $type;

		#
		# children first
		#

		if (!empty($attributes[self::CHILDREN]))
		{
			$this->children = [];
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
				static $translate = [

					self::TYPE_CHECKBOX => [ 'input', 'checkbox' ],
					self::TYPE_RADIO => [ 'input', 'radio' ],

				];

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

				$attributes += [ 'rows' => 10, 'cols' => 76 ];
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
		return isset($this->attributes[$attribute]);
	}

	/**
	 * Returns the value of an attribute.
	 *
	 * @param string $attribute
	 * @param mixed|null $default The default value of the attribute.
	 *
	 * @return mixed The value of the attribute, or null if is not set.
	 */
	public function offsetGet($attribute, $default = null)
	{
		return isset($this->attributes[$attribute]) ? $this->attributes[$attribute] : $default;
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
				$this->children = [];
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

		$this->attributes[$attribute] = $value;
	}

	/**
	 * Removes an attribute.
	 *
	 * @param string $attribute The name of the attribute.
	 */
	public function offsetUnset($attribute)
	{
		unset($this->attributes[$attribute]);
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

	private $_dataset;

	/**
	 * Returns the {@link Dataset} of the element.
	 *
	 * @return Dataset
	 */
	protected function get_dataset()
	{
		if (!$this->_dataset)
		{
			$this->_dataset = new Dataset($this);
		}

		return $this->_dataset;
	}

	/**
	 * Sets the dataset of the element.
	 *
	 * @param array|Dataset #dataset
	 *
	 * @return Dataset
	 */
	protected function set_dataset($dataset)
	{
		if (!($dataset instanceof Dataset))
		{
			$dataset = new Dataset($this, $dataset);
		}

		$this->_dataset = $dataset;
	}

	/**
	 * Returns the attributes of the element.
	 *
	 * @return array
	 */
	protected function get_attributes()
	{
		return $this->attributes;
	}

	/**
	 * Returns the element's id.
	 *
	 * If the element's id is empty, a unique id is generated and added to its attributes.
	 *
	 * @return string
	 */
	protected function lazy_get_id()
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
	protected $class_names = [];

	/**
	 * Returns the value of the "class" attribute.
	 *
	 * @return string
	 */
	protected function get_class()
	{
		$class_names = $this->alter_class_names($this->class_names);

		return $this->render_class($class_names);
	}

	/**
	 * Sets the value of the "class" attribute.
	 *
	 * @param string $class
	 */
	protected function set_class($class)
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
	 * empty then the class name is discarded. If the value is `true` the identifier of the class
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
	 */
	public function adopt($child)
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
	 * @return array
	 */
	public function get_ordered_children()
	{
		if (!$this->children)
		{
			return [];
		}

		return sort_by_weight($this->children, function($v) {

			return $v instanceof self ? ($v[self::WEIGHT] ?: 0) : 0;

		});
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

		$options = $this[self::OPTIONS] ?: [];
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
					$label = escape($label);
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

		$child = new Element('input', [

			'type' => 'checkbox',
			'readonly' => $readonly

		]);

		#
		# create the inner content of our element
		#

		$html = '';
		$disabled_list = $this[self::OPTIONS_DISABLED];

		foreach ($this[self::OPTIONS] as $option_name => $label)
		{
			$child[self::LABEL] = $label;
			$child['name'] = $name ? $name . '[' . $option_name . ']' : $option_name;
			$child['checked'] = !empty($selected[$option_name]);
			$child['disabled'] = $disabled || !empty($disabled_list[$option_name]);
			$child['data-key'] = $option_name;
			$child['data-name'] = $name ?: $option_name;

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

		$child = new Element('input', [

			'type' => 'radio',
			'name' => $name,
			'readonly' => $readonly

		]);

		#
		# create the inner content of our element
		#
		# add our options as children
		#

		$html = '';
		$disabled_list = $this[self::OPTIONS_DISABLED];

		foreach ($this[self::OPTIONS] as $value => $label)
		{
			if ($label && !is_object($label) && $label{0} == '.')
			{
				$label = $this->t(substr($label, 1), [], [ 'scope' => 'element.option' ]);
			}

			$child[self::LABEL] = $label;
			$child['value'] = $value;
			$child['checked'] = (string) $value === (string) $selected;
			$child['disabled'] = $disabled || !empty($disabled_list[$value]);

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
				$attributes[$attribute] = $this->t($value, [], [ 'scope' => 'element.title' ]);
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
	 * Attributes with `false` or `null` values as well as custom attributes are discarded.
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
				throw new \InvalidArgumentException(format('Invalid value for attribute %attribute: :value', [ 'attribute' => $attribute, 'value' => $value ]));
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
	 * The method might add the `default-value` and {@link IS_ATTRIBUTE} keys.
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

		return $dataset;
	}

	/**
	 * Renders dataset.
	 *
	 * The dataset is rendered as a series of "data-*" attributes. Values of type array are
	 * encoded using the {@link json_encode()} function. Attributes with null values are discarded,
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

		if ($inner === null && $this->tag_name === 'div')
		{
			throw new ElementIsEmpty;
		}

		$attributes = [];
		$dataset = [];

		foreach ($this->attributes as $attribute => $value)
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

		if ($label || $label === '0')
		{
			$label = $this->t($label, [], [ 'scope' => 'element.label' ]);
			$html = $this->decorate_with_label($html, $label);
		}

		#
		# add inline help
		#

		$help = $this[self::INLINE_HELP];

		if ($help)
		{
			$help = $this->t($help, [], [ 'scope' => 'element.inline_help' ]);
			$html = $this->decorate_with_inline_help($html, $help);
		}

		#
		# add description
		#

		$description = $this[self::DESCRIPTION];

		if ($description)
		{
			$description = $this->t($description, [], [ 'scope' => 'element.description' ]);
			$html = $this->decorate_with_description($html, $description);
		}

		#
		# add legend
		#

		$legend = $this[self::LEGEND];

		if ($legend)
		{
			$legend = $this->t($legend, [], [ 'scope' => 'element.legend' ]);
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

		if ($this['disabled'])
		{
			$class .= ' disabled';
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
		return $html . '<div class="help-inline text-muted">' . $help . '</div>';
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
		return $html . '<div class="element-description text-muted">' . $description . '</div>';
	}

	/**
	 * Renders the element into an HTML string.
	 *
	 * Before the element is rendered the method  {@link handle_assets()} is invoked.
	 *
	 * The inner HTML is rendered by the {@link render_inner_html()} method. The outer HTML is
	 * rendered by the {@link render_outer_html()} method. Finally, the HTML is decorated by
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
	 * Translates and formats a string.
	 *
	 * The method uses the translator specified by {@link TRANSLATOR} or the {@link t()} function
	 * if it is not specified.
	 *
	 * @see \Brickrouge\t
	 *
	 * @param string $pattern The native string to translate.
	 * @param array $args An array of replacements to make after the translation. The replacement is
	 * handled by the {@link format()} function.
	 * @param array $options An array of additional options, with the following elements:
	 * - 'default': The default string to use if the translation failed.
	 * - 'scope': The scope of the translation.
	 *
	 * @return string
	 */
	public function t($pattern, array $args = [], array $options = [])
	{
		/* @var $translator callable */
		$translator = $this[self::TRANSLATOR];

		return $translator ? $translator($pattern, $args, $options) : t($pattern, $args, $options);
	}
}
