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
 * @see http://dev.w3.org/html5/spec/Overview.html#embedding-custom-non-visible-data-with-the-data-attributes
 */
class Element extends \ICanBoogie\Object implements \ArrayAccess
{
	#
	# special elements
	#

	const E_CHECKBOX = '#checkbox';
	const E_CHECKBOX_GROUP = '#checkbox-group';
	const E_FILE = '#file';
	const E_HIDDEN = '#hidden';
	const E_PASSWORD = '#password';
	const E_RADIO = '#radio';
	const E_RADIO_GROUP = '#radio-group';
	const E_SUBMIT = '#submit';
	const E_TEXT = '#text';

	#
	# special tags
	#

	const T_CHILDREN = '#children';
	const T_DATASET = '#dataset';
	const T_DEFAULT = '#default';
	const T_DESCRIPTION = '#description';
	const T_FILE_WITH_LIMIT = '#element-file-with-limit';
	const T_FILE_WITH_REMINDER = '#element-file-with-reminder';
	const T_GROUP = '#group';
	const T_GROUPS = '#groups';
	const T_INLINE_HELP = '#inline-help';
	const T_TYPE = '#type';

	/**
	 * The T_INNER_HTML tag is used to define the inner HTML of an element.
	 * If the value of the tag is NULL, the markup will be self-closing.
	 */

	const T_INNER_HTML = '#innerHTML';
	const T_LABEL = '#element-label';
	const T_LABEL_POSITION = '#element-label-position';
	const T_LABEL_SEPARATOR = '#element-label-separator';
	const T_LABEL_MISSING = '#element-label-missing'; // TODO: use this in validation
	const T_LEGEND = '#element-legend';
	const T_REQUIRED = '#required';
	const T_OPTIONS = '#element-options';
	const T_OPTIONS_DISABLED = '#element-options-disabled';

	/**
	 * Define a validator for the object. The validator is defined using an
	 * array made of a callback and a possible userdata array.
	 *
	 */

	const T_VALIDATOR = '#validator';
	const T_VALIDATOR_OPTIONS = '#validator-options';
	const T_VERIFY = '#element-verify';
	const T_WEIGHT = '#weight';

	static $inputs = array('button', 'form', 'input', 'option', 'select', 'textarea');
	static private $has_attribute_value = array('button', 'input', 'option');

	#
	#
	#

	public $type;
	public $tagName;
	public $children = array();
	public $dataset = array();

	protected $tags = array();
	protected $classes = array();
	protected $innerHTML = null;

	public function __construct($type, $tags=array())
	{
		if ($tags === null)
		{
			$tags = array();
		}

		$this->type = $type;
		$this->tags = $tags;

		#
		# prepare special elements
		#

		switch ((string) $type)
		{
			case self::E_CHECKBOX:
			case self::E_RADIO:
			case self::E_SUBMIT:
			case self::E_TEXT:
			case self::E_HIDDEN:
			case self::E_PASSWORD:
			{
				static $translate = array
				(
					self::E_CHECKBOX => array('input', 'checkbox'),
					self::E_RADIO => array('input', 'radio'),
					self::E_SUBMIT => array('button', 'submit'),
					self::E_TEXT => array('input', 'text'),
					self::E_HIDDEN => array('input', 'hidden'),
					self::E_PASSWORD => array('input', 'password')
				);

				$this->tagName = $translate[$type][0];
				$tags['type'] = $translate[$type][1];

				if ($type == self::E_SUBMIT)
				{
					$tags += array
					(
						self::T_INNER_HTML => t('Send')
					);
				}
			}
			break;

			case self::E_CHECKBOX_GROUP:
			{
				$this->tagName = 'div';
				$this->addClass('checkbox-group');
			}
			break;

			case self::E_FILE:
			{
				$this->tagName = 'input';

				$tags['type'] = 'file';

				$tags += array('size' => 40);
			}
			break;

			case self::E_RADIO_GROUP:
			{
				$this->tagName = 'div';
				$this->addClass('radio-group');
			}
			break;

			case 'textarea':
			{
				$this->tagName = 'textarea';
				$this->innerHTML = '';

				$tags += array('rows' => 10, 'cols' => 76);
			}
			break;

			default:
			{
				$this->tagName = $type;
			}
			break;
		}

		$this->set($tags);
	}

	/**
	 * @param offset
	 */
	public function offsetExists($offset)
	{
		return isset($this->tags[$offset]);
	}

	/**
	 * @param offset
	 */
	public function offsetGet($offset)
	{
		return isset($this->tags[$offset]) ? $this->tags[$offset] : null;
	}

	/**
	 * @param offset
	 * @param value
	 */
	public function offsetSet($offset, $value)
	{
		$this->tags[$offset] = $value;
	}

	/**
	 * @param offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->tags[$offset]);
	}

	static protected $auto_element_id = 1;

	static public function auto_element_id()
	{
		return 'element-autoid-' . self::$auto_element_id++;
	}

	/**
	 * Returns the element's id.
	 *
	 * If the element's id is empty, a unique id is generated and added to its tags.
	 *
	 * @return string
	 */
	protected function __get_id()
	{
		$id = $this->get('id');

		if (!$id)
		{
			$id = self::auto_element_id();

			$this->tags['id'] = $id;
		}

		return $id;
	}

	/**
	 * The set() method is used to set, unset (nullify) and modify a tag
	 * of an element.
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
			$this->tags[$name] = $value;
		}

		switch ($name)
		{
			case self::T_CHILDREN:
			{
				$this->children = array();
				$this->addChildren($value);
			}
			break;

			case self::T_DATASET:
			{
				$this->dataset = $value;
			}
			break;

			case self::T_INNER_HTML:
			{
				$this->innerHTML = $value;
			}
			break;

			case 'class':
			{
				$classes = explode(' ', $value);
				$classes = array_map('trim', $classes);

				$this->classes += array_flip($classes);
			}
			break;

			case 'id':
			{
				unset($this->$name);
			}
			break;
		}
	}

	/**
	 * The get() method is used to get to value of a tag. If the tag is not
	 * set, `null` is returned. You can provide a default value which is returned
	 * instead of `null` if the tag is not set.
	 */
	public function get($name, $default=null)
	{
		return isset($this->tags[$name]) ? $this->tags[$name] : $default;
	}

	/**
	 * Add a CSS class to the element.
	 * @param: $class
	 */
	public function addClass($class)
	{
		$this->classes[$class] = true;
	}

	/**
	 * Remove a CSS class from the element.
	 * @param $class
	 */
	public function removeClass($class)
	{
		unset($this->classes[$class]);
	}

	/**
	 * Tests the element to see if it has the specified class name.
	 *
	 * @param string $class_name
	 *
	 * @return boolean true if the element has the class name, false otherwise.
	 */
	public function has_class($class_name)
	{
		return isset($this->classes[$class_name]);
	}

	/**
	 * Collect the CSS classes of the element.
	 *
	 * The method returns a single string made of the classes joined together.
	 *
	 * @return string
	 */

	protected function compose_class()
	{
		$value = $this->get('class');
		$classes = $this->classes;

		if ($value)
		{
			$add = explode(' ', $value);
			$add = array_map('trim', $add);

			$classes = array_flip($add) + $classes;
		}

		return implode(' ', array_keys($classes));
	}

	protected function handleValue(&$tags)
	{
		$value = $this->get('value');

		if ($value === null)
		{
			$default = $this->get(self::T_DEFAULT);

			if ($default)
			{
				if ($this->type == self::E_CHECKBOX)
				{
					// TODO-20100108: we need to check this situation further more

					//$this->set('checked', $default);
				}
				else
				{
					$this->set('value', $default);
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
	public function addChild($child, $name=null)
	{
		if ($name)
		{
			if (is_object($child))
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

	public function addChildren(array $children)
	{
		foreach ($children as $name => $child)
		{
			$this->addChild($child, is_numeric($name) ? null : $name);
		}
	}

	/**
	 * Returns the children of the element.
	 *
	 * The children are ordered according to their weight.
	 */

	public function getChildren()
	{
		Debug::trigger('Use the get_ordered_children() method');

		return $this->get_ordered_children();
	}

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
			$weight = is_object($child) ? $child->get(self::T_WEIGHT, 0) : 0;

			if (is_string($weight))
			{
				$with_relative_positions[] = $child;

				continue;
			}

			$by_weight[$weight][$name] = $child;
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
				list($target, $position) = explode(':', $child->get(self::T_WEIGHT)) + array(1 => 'after');

				$rc = wd_array_insert($rc, $target, $child, $child->get('name'), $position == 'after');
			}
		}

		return $rc;
	}

	public function get_named_elements()
	{
		$rc = array();

		$this->walk(array($this, 'get_named_elements_callback'), array(&$rc), 'name');

		return $rc;
	}

	private function get_named_elements_callback(Element $element, $userdata, $stop_value)
	{
		$userdata[0][$stop_value] = $element;
	}

	/*
	**

	CONTEXT

	**
	*/

	protected $pushed_tags = array();
	protected $pushed_children = array();
	protected $pushed_innerHTML = array();

	public function contextPush()
	{
		array_push($this->pushed_tags, $this->tags);
		array_push($this->pushed_children, $this->children);
		array_push($this->pushed_innerHTML, $this->innerHTML);
	}

	public function contextPop()
	{
		$this->tags = array_pop($this->pushed_tags);
		$this->children = array_pop($this->pushed_children);
		$this->innerHTML = array_pop($this->pushed_innerHTML);
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
	* Returns the HTML representation of the element's contents.
	*
	* Remember that if the element has a null content it is considered as self closing.
	*
	* @return string The content of the element.
	*/
	protected function render_inner_html()
	{
		$rc = null;

		$children = $this->get_ordered_children();

		if ($children)
		{
			foreach ($children as $child)
			{
				$rc .= $this->render_child($child);
			}
		}
		else
		{
			$rc = $this->innerHTML;
		}

		return $rc;
	}

	/**
	 * Returns the HTML representation of the element and its contents.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		#
		# In order to allow further customization, the contents of the element is created before
		# its markup.
		#

		try
		{
			$inner = $this->render_inner_html();
		}
		catch (\Exception $e)
		{
			$inner = (string) $e;
		}

		#
		#
		#

		$rc = '<' . $this->tagName;

		#
		# class
		#

		$class = $this->compose_class();

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

			if ($attribute == 'value' && !in_array($this->tagName, self::$has_attribute_value))
			{
				continue;
			}

			#
			# We discard the `disabled`, `name` and `value` attributes for non input type elements
			#

			if (($attribute == 'disabled' || $attribute == 'name') && !in_array($this->tagName, self::$inputs))
			{
				continue;
			}

			if ($attribute == 'title' && is_string($value) && $value{0} == '.')
			{
				$value = t(substr($value, 1), array(), array('scope' => array('element', 'title')));
			}

			#
			# attributes with the value TRUE are translated to XHTML standard
			# e.g. readonly="readonly"
			#

			if ($value === true)
			{
				$value = $attribute;
			}

			$rc .= ' ' . $attribute . '="' . (is_numeric($value) ? $value : wd_entities($value)) . '"';
		}

		foreach ($this->dataset as $name => $value)
		{
			if (is_array($value))
			{
				$value = json_encode($value);
			}

			if ($value === null)
			{
				continue;
			}

			$rc .= ' data-' . $name . '="' . (is_numeric($value) ? $value : wd_entities($value)) . '"';
		}

		#
		# if the inner HTML of the element is null, the element is self closing
		#

		if ($inner === null)
		{
			$rc .= ' />';
		}
		else
		{
			$rc .= '>' . $inner . '</' . $this->tagName . '>';
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

		$label = $this->get(self::T_LABEL);

		if ($label)
		{
			if ($label{0} == '.')
			{
				$label = t(substr($label, 1), array(), array('scope' => array('element', 'label')));
			}
			else
			{
				$label = t($label);
			}

			$html = $this->decorate_with_label($html, $label);
		}

		#
		# add inline help
		#

		$help = $this->get(self::T_INLINE_HELP);

		if ($help)
		{
			if ($help{0} == '.')
			{
				$help = t(substr($help, 1), array(), array('scope' => array('element', 'help')));
			}

			$html = $this->decorate_with_inline_help($html, $help);
		}

		#
		# add description
		#

		$description = $this->get(self::T_DESCRIPTION);

		if ($description)
		{
			if ($description{0} == '.')
			{
				$description = t(substr($description, 1), array(), array('scope' => array('element', 'description')));
			}

			$html = $this->decorate_with_description($html, $description);
		}

		#
		# add legend
		#

		$legend = $this->get(self::T_LEGEND);

		if ($legend)
		{
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
		$is_required = $this->get(self::T_REQUIRED);
		$position = $this->get(self::T_LABEL_POSITION, 'after');
		$separator = $this->get(self::T_LABEL_SEPARATOR, true);

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
			$label = '<span class="label">' . $label . '</span>';
		}

		// TODO-20100714: T_LABEL_SEPARATOR is not used now, look out for consequences

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
				$html = '';
// 				$html  = '<div class="element-label">';
				$html .= '<label class="' . $class . ' above">' . $label . '</label>';
// 				$html .= '</div>';

// 				$html .= '<div class="element-element">';
				$html .= $content;
// 				$html .= '</div>';
			}
			break;

			case 'before':
			{
				$html  = '<label class="' . $class . '">';
				$html .= $label;

				/*
				if ($separator)
				{
					$html .= '&nbsp;:';
				}
				*/

				$html .= ' ' . $content;
				$html .= '</label>';
			}
			break;

			case 'after':
			default:
			{
				$html  = '<label class="' . $class . '">' . $content . ' ' . $label . '</label>';
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
		return $html . '<div class="element-description">' . $description . '</div>';
	}

	static private $classes_added_assets;

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
		global $document;

		if (isset($document))
		{
			$class = get_class($this);

			if (!isset(self::$classes_added_assets))
			{
				self::$classes_added_assets = true;

				$assets = $this->assets;

				$document->add_assets($assets);
			}
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

		if (in_array($this->tagName, $valued_elements))
		{
			$this->handleValue($tags);
		}

		#
		#
		#

		switch ($this->type)
		{
			case self::E_CHECKBOX:
			{
				$this->contextPush();

				if ($this->get(self::T_DEFAULT) && $this->get('checked') === null)
				{
					$this->set('checked', true);
				}

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case self::E_CHECKBOX_GROUP:
			{
				$this->contextPush();

				$this->handleValue($tags);

				#
				# get the name and selected value for our children
				#

				$name = $this->get('name');
				$selected = $this->get('value', array());
				$disabled = $this->get('disabled', false);
				$readonly = $this->get('readonly', false);

				#
				# and remove them from our attribute list
				#

				$this->set
				(
					array
					(
						'name' => null,
						'value' => null,
						'disabled' => null,
						'readonly' => null
					)
				);

				#
				# this is the 'template' child
				#

				$child = new Element
				(
					'input', array
					(
						'type' => 'checkbox',
						'disabled' => $disabled,
						'readonly' => $readonly
					)
				);

				#
				# create the inner content of our element
				#

				$inner = null;
				$disableds = $this->get(self::T_OPTIONS_DISABLED);

				foreach ($tags[self::T_OPTIONS] as $option_name => $label)
				{
					$child->set
					(
						array
						(
							self::T_LABEL => $label,
							'name' => $name . '[' . $option_name . ']',
							'checked' => !empty($selected[$option_name]),
							'disabled' => !empty($disableds[$option_name])
						)
					);

					$inner .= $child;
				}

				$this->innerHTML .= $inner;

				#
				# make our element
				#

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case self::E_FILE:
			{
				$rc .= '<div class="wd-file">';

				#
				# the T_FILE_WITH_REMINDER tag can be used to add a disabled text input before
				# the file element. this text input is used to display the current value of the
				# file element.
				#

				$reminder = $this->get(self::T_FILE_WITH_REMINDER);

				if ($reminder === true)
				{
					$reminder = $this->get('value');
				}

				if ($reminder)
				{
					$rc .= '<div class="reminder">';

					$rc .= new Element
					(
						Element::E_TEXT, array
						(
							'value' => $reminder,
							'disabled' => true,
							'size' => $this->get('size', 40)
						)
					);

					$rc .= ' ';

					$rc .= new Element
					(
						'a', array
						(
							self::T_INNER_HTML => 'Télécharger',

							'href' => $reminder,
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
				# the T_FILE_WITH_LIMIT tag can be used to add a little text after the element
				# reminding the maximum file size allowed for the upload
				#

				$limit = $this->get(self::T_FILE_WITH_LIMIT);

				if ($limit)
				{
					if ($limit === true)
					{
						$limit = ini_get('upload_max_filesize') * 1024;
					}

					$limit = wd_format_size($limit * 1024);

					$rc .= PHP_EOL;
					$rc .= '<div class="limit">';
					$rc .= t('The maximum file size must be less than :size.', array(':size' => $limit));
					$rc .= '</div>';
				}

				$rc .= '</div>';
			}
			break;

			case self::E_RADIO_GROUP:
			{
				$this->contextPush();

				$this->handleValue($tags);

				#
				# get the name and selected value for our children
				#

				$name = $this->get('name');
				$selected = $this->get('value');
				$disabled = $this->get('disabled', false);
				$readonly = $this->get('readonly', false);

				#
				# and remove them from our attribute list
				#

				$this->set
				(
					array
					(
						'name' => null,
						'value' => null,
						'disabled' => null,
						'readonly' => null
					)
				);

				#
				# this is the 'template' child
				#

				$child = new Element
				(
					'input', array
					(
						'type' => 'radio',
						'name' => $name,
						'disabled' => $disabled,
						'readonly' => $readonly
					)
				);

				#
				# --create the inner content of our element
				#
				# add our options as children
				#

				$disableds = $this->get(self::T_OPTIONS_DISABLED);

				foreach ($tags[self::T_OPTIONS] as $value => $label)
				{
					if ($label && $label{0} == '.')
					{
						$label = t(substr($label, 1), array(), array('scope' => array('element', 'option')));
					}

					$child->set
					(
						array
						(
							self::T_LABEL => $label,
							'value' => $value,
							'checked' => (string) $value === (string) $selected,
							'disabled' => !empty($disableds[$value])
						)
					);

					$this->children[] = clone $child;
				}

				#
				# make our element
				#

				$rc = $this->render_outer_html();

				$this->contextPop();
			}
			break;

			case self::E_PASSWORD:
			{
				$this->contextPush();

				#
				# for security reason, the value of the password is emptied
				#

				$this->set('value', '');

				$rc = $this->render_outer_html();

				// FIXME: That's so lame !

				if (isset($tags[self::T_VERIFY]))
				{

					$name = $this->get('name');
					$label = t('confirm');

					if ($this->get(self::T_REQUIRED))
					{
						$label = '<sup>*</sup> ' . $label;
					}

					$this->set('name', $name . '-confirm');

					$rc .= ' <label>';
					$rc .= $label;
					$rc .= '&nbsp;:';
					$rc .= ' ' . $this->render_outer_html();
					$rc .= '</label>';
				}

				$this->contextPop();
			}
			break;

			case 'select':
			{
				$this->contextPush();

				#
				# get the name and selected value for our children
				#

				$selected = $this->get('value');

				#
				# this is the 'template' child
				#

				$child = new Element('option');

				#
				# create the inner content of our element
				#

				$inner = '';

				$options = $this->get(self::T_OPTIONS, array());
				$disabled = $this->get(self::T_OPTIONS_DISABLED);

				foreach ($options as $value => $label)
				{
					#
					# value is casted to a string so that we can handle null value and compare '0' with 0
					#

					$child->set
					(
						array
						(
							'value' => $value,
							'selected' => (string) $value === (string) $selected,
							'disabled' => !empty($disabled[$value])
						)
					);

					if ($label)
					{
						if ($label{0} == '.')
						{
							$label = t(substr($label, 1), array(), array('scope' => array('element', 'option')));
						}

						$label = wd_entities($label);
					}
					else
					{
						$label = '&nbsp;';
					}

					$child->innerHTML = $label;

					$inner .= $child;
				}

				$this->innerHTML .= $inner;

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

				$this->innerHTML = wd_entities($this->get('value', ''));

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
				catch (\Exception $e)
				{
					$rc = (string) $e;
				}
			}
			break;
		}

		return $this->decorate($rc);
	}

	/**
	 * Validate the value of the object.
	 *
	 * This function uses the validator defined using the T_VALIDATOR tag to validate
	 * its value.
	 *
	 * @param $value
	 * @return boolean Return TRUE is the validation succeed.
	 */

	public function validate($value)
	{
		$validator = $this->get(self::T_VALIDATOR);
		$options = $this->get(self::T_VALIDATOR_OPTIONS);

		if ($validator)
		{
			list($callback, $params) = $validator + array(1 => array());

			return call_user_func($callback, $this, $value, $params);
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
			case self::E_CHECKBOX_GROUP:
			{
				if (isset($options['max-checked']))
				{
					$limit = $options['max-checked'];

					if (count($value) > $limit)
					{
						$this->form->log
						(
							$this->name, 'Le nombre de choix possible pour le champ %name est limité à :limit', array
							(
								'%name' => Form::selectElementLabel($this),
								':limit' => $limit
							)
						);

						return false;
					}
				}
			}
			break;
		}

		return true;
	}

	/**
	 * Walk thought the elements of the element's tree applying a function to each one of them.
	 *
	 * The callback is called with the element, the userdata and the stop value for the
	 * element (which is null if @stop is null).
	 *
	 * If @stop is defined, only element having a non-null @stop attribute are called.
	 *
	 * @param $callback
	 * @param $userdata
	 * @param $stop
	 */

	public function walk($callback, $userdata, $stop=null)
	{
		#
		# if the element has children, we walk them first, the walktrought is bubbling.
		#

		foreach ($this->children as $child)
		{
			#
			# Only instances of the Element class are walkable.
			#

			if (!($child instanceof Element))
			{
				continue;
			}

			$child->walk($callback, $userdata, $stop);
		}

		#
		# the callback is not called for the element, if its 'stop' attribute is null
		#

		$stop_value = null;

		if ($stop)
		{
			$stop_value = $this->get($stop);

			if ($stop_value === null)
			{
				return;
			}
		}

		call_user_func($callback, $this, $userdata, $stop_value);
	}

	protected function __get_assets()
	{
		return array('css' => array(), 'js' => array());
	}

	// FIXME-20110204: hou que c'est vilain !

	static protected function translate_label($label)
	{
		if (is_string($label) && $label{0} == '.')
		{
			return t(substr($label, 1), array(), array('scope' => array('element', 'label')));
		}

		if (!is_array($label))
		{
			return t($label, array(), array('scope' => array('element', 'label')));
		}

		return t($label[0], array(), array('scope' => $label[1]));
	}
}