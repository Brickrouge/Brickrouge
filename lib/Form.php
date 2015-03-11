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
 * A `<FORM>` element.
 */
class Form extends Element implements Validator
{
	/**
	 * Set to true to disable all the elements of the form.
	 */
	const DISABLED = '#form-disabled';

	/**
	 * Used to provide hidden values. Each key/value pair of the array is used to create
	 * an hidden input element with key as `name` attribute and value as `value` attribute.
	 */
	const HIDDENS = '#form-hiddens';

	/**
	 * Used by elements to define a form label, this is different from the
	 * {@link Element::LABEL}, which wraps the element in a `<LABEL>` element, the form label is
	 * associated with the element but its layout depend on the form renderer.
	 */
	const LABEL = '#form-label';

	/**
	 * Complement to the {@link LABEL} tag. Its layout depends on the form renderer.
	 */
	const LABEL_COMPLEMENT = '#form-label-complement';

	/**
	 * If true possible alert messages are not displayed.
	 */
	const NO_LOG = '#form-no-log';

	/**
	 * Values for the elements of the form. The form recursively iterates through its
	 * children to set their values, if their values it not already set (e.g. non null).
	 */
	const VALUES = '#form-values';

	/**
	 * The class name of the renderer to use to render the children of the form. If no
	 * renderer is defined, children are simply concatenated.
	 */
	const RENDERER = '#form-renderer';

	/**
	 * Defines the actions of the form.
	 *
	 * @see render_actions()
	 */
	const ACTIONS = '#form-actions';

	/**
	 * Defines form errors.
	 */
	const ERRORS = '#form-errors';

	/**
	 * Returns a unique form name.
	 *
	 * @return string
	 */
	static protected function get_auto_name()
	{
		return 'form-autoname-' . self::$auto_name_index++;
	}

	static protected $auto_name_index = 1;

	/**
	 * Hidden values, initialized with the {@link HIDDENS} tag.
	 *
	 * @var array[string]string
	 */
	public $hiddens = [];

	/**
	 * Name of the form.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Default attributes are added to those provided using a union:
	 *
	 * - `action`: If the `id` attribute is provided, `action` is set to "#<id>".
	 * - `method`: "POST"
	 * - `enctype`: "multipart/form-data"
	 * - `name`: The value of the `id` attribute or a name generated with the {@link get_auto_name()} method
	 *
	 * If `method` is different than "POST" then the `enctype` attribute is unset.
	 *
	 * @param array $attributes
	 */
	public function __construct(array $attributes=[])
	{
		$attributes += [

			'action' => isset($attributes['id']) ? '#' . $attributes['id'] : '',
			'method' => 'POST',
			'enctype' => 'multipart/form-data',
			'name' => isset($attributes['id']) ? $attributes['id'] : self::get_auto_name()

		];

		if (strtoupper($attributes['method']) != 'POST')
		{
			unset($attributes['enctype']);
		}

		$this->name = $attributes['name'] ?: self::get_auto_name();

		parent::__construct('form', $attributes);
	}

	/**
	 * Renders the object into an HTML string.
	 *
	 * Before rendering the object form elements are altered according to the {@link VALUES} and
	 * {@link DISABLED} tags and previous validation errors.
	 */
	public function __toString()
	{
		$values = $this[self::VALUES];
		$disabled = $this[self::DISABLED];

		$name = $this->name;
		$errors = null;

		if ($name)
		{
			$errors = retrieve_form_errors($name);
		}

		if ($values || $disabled || $errors)
		{
			if ($values)
			{
				$values = array_flatten($values);
			}

			if (!$errors)
			{
				$errors = new Errors();
			}

			$this->alter_elements($values, $disabled, $errors);
		}

		return parent::__toString();
	}

	/**
	 * Override the method to map the {@link HIDDENS} tag to the {@link $hiddens} property.
	 *
	 * @inheritdoc
	 */
	public function offsetSet($attribute, $value)
	{
		parent::offsetSet($attribute, $value);

		if ($attribute == self::HIDDENS)
		{
			$this->hiddens = $value;
		}
	}

	/**
	 * Returns a recursive iterator.
	 *
	 * A {@link RecursiveIterator} iterator is created to traverse the children of the form.
	 *
	 * The recursive iterator is created with the {@link SELF_FIRST} mode.
	 *
	 * @return \RecursiveIteratorIterator
	 */
	public function getIterator()
	{
		return new \RecursiveIteratorIterator(new RecursiveIterator($this), \RecursiveIteratorIterator::SELF_FIRST);
	}

	/**
	 * @var Element[] The required elements of the form.
	 */
	protected $required = [];

	/**
	 * @var string[] Booleans found in the form.
	 */
	protected $booleans = [];

	/**
	 * @var Element[] Elements of the form with a validator.
	 */
	protected $validators = [];

	/**
	 * @var callable Validator callback of the form.
	 */
	protected $validator;

	/**
	 * The method alters the {@link $required}, {@link $validators} and {@link $validator}
	 * properties required for the serialization.
	 *
	 * The following properties are exported: name, required, validators and validator.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		$required = [];
		$booleans = [];
		$validators = [];

		foreach ($this as $element)
		{
			$name = $element['name'];

			if (!$name)
			{
				continue;
			}

			if ($element[Element::REQUIRED])
			{
				$required[$name] = self::select_element_label($element);
			}

			if ($element->tag_name == 'input')
			{
				if ($element['type'] == 'checkbox')
				{
					$booleans[$name] = true;
				}
			}
			else if ($element->type == Element::TYPE_CHECKBOX_GROUP)
			{
				foreach ($element[self::OPTIONS] as $option_name => $dummy)
				{
					$booleans[$name . '[' . $option_name . ']'] = true;
				}
			}

			if ($element[self::VALIDATOR] || $element[self::VALIDATOR_OPTIONS] || $element instanceof Validator)
			{
				$validators[$name] = $element;
			}
		}

		$this->required = $required;
		$this->booleans = $booleans;
		$this->validators = $validators;
		$this->validator = $this[self::VALIDATOR];

		#
		# we return the variables to serialize, we only export variables needed for later
		# validation.
		#

		return [ 'name', 'required', 'booleans', 'validators', 'validator' ];
	}

	/**
	 * If a rendered is defined it is used to render the children.
	 *
	 * The rendered is defined using the {@link RENDERER} attribute.
	 *
	 * @inheritdoc
	 */
	protected function render_children(array $children)
	{
		/* @var $renderer callable*/

		$renderer = $this[self::RENDERER];

		if ($renderer)
		{
			if (is_string($renderer))
			{
				$class = $renderer;

				if (!class_exists($class))
				{
					$class = 'Brickrouge\Renderer\\' . $class;
				}

				$renderer = new $class();
			}

			return $renderer($this);
		}

		return parent::render_children($children);
	}

	/**
	 * Add hidden input elements and log messages to the inner HTML of the element
	 * being converted to a string.
	 *
	 * @inheritdoc
	 */
	protected function render_inner_html()
	{
		$inner_html = parent::render_inner_html();
		$hiddens = $this->render_hiddens($this->hiddens);

		if (!$inner_html)
		{
			$this->add_class('has-no-content');
		}
		else
		{
			$this->remove_class('has-no-content');
		}

		#
		# alert message
		#

		$alert = null;

		if (!$this[self::NO_LOG])
		{
			$name = $this->name;
			$errors = retrieve_form_errors($name);

			if ($errors)
			{
				$alert = $this->render_errors($errors);

				store_form_errors($name, []); // reset form errors.
			}
		}

		#
		# actions
		#

		$actions = $this[self::ACTIONS];

		if ($actions)
		{
			$this->add_class('has-actions');

			$actions = $this->render_actions($actions);
		}
		else
		{
			$this->remove_class('has-actions');
		}

		if (!$inner_html && !$actions)
		{
			throw new ElementIsEmpty();
		}

		return $hiddens . $alert . $inner_html . $actions;
	}

	/**
	 * Renders errors as an HTML element.
	 *
	 * An {@link Alert} object is used to render the provided errors.
	 *
	 * @param string|\ICanBoogie\Errors $errors.
	 *
	 * @return string
	 */
	protected function render_errors($errors)
	{
		return (string) new Alert($errors);
	}

	/**
	 * Renders actions using an {@link Actions} element.
	 *
	 * @param mixed $actions
	 *
	 * @return string Return the actions block.
	 */
	protected function render_actions($actions)
	{
		return (string) new Actions($actions, [ 'class' => 'form-actions' ]);
	}

	/**
	 * Renders hidden values.
	 *
	 * @param array $hiddens
	 *
	 * @return string
	 */
	protected function render_hiddens(array $hiddens)
	{
		$html = '';

		foreach ($hiddens as $name => $value)
		{
			#
			# we skip undefined values
			#

			if ($value === null)
			{
				continue;
			}

			$html .= '<input type="hidden" name="' . escape($name) . '" value="' . escape($value) . '" />';
		}

		return $html;
	}

	/**
	 * Alters the elements according to the state of the form.
	 *
	 * @param array $values
	 * @param bool $disabled true if the form is disabled, false otherwise.
	 * @param array|\ICanBoogie\Errors $errors The validation errors.
	 */
	protected function alter_elements($values, $disabled, $errors)
	{
		foreach ($this as $element)
		{
			#
			# disable the element if the form is disabled.
			#

			if ($disabled)
			{
				$element['disabled'] = true;
			}

			$name = $element['name'];

			if (!$name)
			{
				continue;
			}

			#
			# if the element is referenced in the errors, we set its state to 'error'
			#

			if (isset($errors[$name]))
			{
				$element[Element::STATE] = 'error';
			}

			#
			# set value
			#

			if ($values && array_key_exists($name, $values))
			{
				$type = $element['type'];
				$value = $values[$name];

				#
				# we don't override the `value` or `checked` attributes if they are already defined
				#

				if ($type == 'checkbox')
				{
					if ($element['checked'] === null)
					{
						$element['checked'] = !empty($value);
					}
				}
				else if ($type == 'radio')
				{
					if ($element['checked'] === null)
					{
						$element_value = $element['value'];
						$element['checked'] = $element_value == $value;
					}
				}
				else if ($element['value'] === null)
				{
					$element['value'] = $value;
				}
			}
		}
	}

	/*
	 * Save and restore.
	 */

	const STORED_KEY_NAME = '_brickrouge_form_key';

	/**
	 * Save the form in the session for future validation.
	 *
	 * @return string The key used to identify the form save in the session.
	 */
	public function save()
	{
		$key = store_form($this);

		$this->hiddens[self::STORED_KEY_NAME] = $key;

		return $this;
	}

	/**
	 * Loads a form previously saved in session.
	 *
	 * @param string $key The key used to identify the form to load, or an array in which
	 * {@link STORED_KEY_NAME} defines the key.
	 *
	 * @return Form
	 *
	 * @throws \Exception if the form cannot be retrieved.
	 */
	static public function load($key)
	{
		if (is_array($key))
		{
			if (empty($key[self::STORED_KEY_NAME]))
			{
				throw new \Exception('The key to retrieve the form is missing.');
			}

			$key = $key[self::STORED_KEY_NAME];
		}

		$form = retrieve_form($key);

		if (!$form)
		{
			throw new \Exception('The form has expired.');
		}

		$form[self::VALIDATOR] = $form->validator;

		return $form;
	}

	/**
	 * Checks if a previously saved form exists for a given key.
	 *
	 * @param string $key The key used to identify the form.
	 *
	 * @return boolean Return `true` if the form exists.
	 */
	static public function exists($key)
	{
		check_session();

		return !empty($_SESSION['brickrouge.saved_forms'][$key]);
	}

	static public function select_element_label($element)
	{
		$label = $element[self::LABEL_MISSING];

		if (!$label)
		{
			$label = $element[Form::LABEL];
		}

		if (!$label)
		{
			$label = $element[Element::LABEL];
		}

		if (!$label)
		{
			$label = $element[self::LEGEND] ?: $label;
		}

		#
		# remove HTML markups from the label
		#

		$label = t($label, [], [ 'scope' => 'element.label' ]);
		$label = strip_tags($label);

		return $label;
	}

	/**
	 * Validates the form using the provided values.
	 *
	 * @inheritdoc
	 */
	public function validate($values, Errors $errors)
	{
		#
		# validation without prior save
		#

		if (empty($values[self::STORED_KEY_NAME]))
		{
			$this->__sleep();
		}

		#
		# we flatten the array so that we can easily get values
		# for keys such as `cars[1][color]`
		#

		$values = array_flatten($values);

		$this->values = $values;

		#
		# process required values
		#

		$validators = $this->validators;

		foreach ($validators as $identifier => $element)
		{
			$element->form = $this;
			$element->name = $identifier;
			$element->label = self::select_element_label($element);
		}

		#
		# process required elements
		#

		$this->validate_required_elements($this->required, $validators, $values, $errors);

		#
		# process elements validators
		#
		# note: If the value for the element is `null` and the value is not required the element's
		# validator is *not* called.
		#

		foreach ($validators as $name => $element)
		{
			$value = isset($values[$name]) ? $values[$name] : null;

			if (($value === null || $value === '') && empty($this->required[$name]))
			{
				continue;
			}

			$element->validate($value, $errors);
		}

		// FIXME-20111013: ICanBoogie won't save the errors in the session, so we have to do it ourselves for now.

		store_form_errors($this->name, $errors);

		if (count($errors))
		{
			return false;
		}

		return parent::validate($values, $errors);
	}

	protected function validate_required_elements(array $required, array &$validators, array $values, Errors $errors)
	{
		$missing = [];

		foreach ($required as $name => $label)
		{
			if (!isset($values[$name]) || (isset($values[$name]) && is_string($values[$name]) && !strlen(trim($values[$name]))))
			{
				$missing[$name] = $this->t($label);

				#
				# The value for this required element is missing.
				# In order to avoid troubles, the element is removed
				# for the validators array.
				#

				unset($validators[$name]);
			}
		}

		if ($missing)
		{
			if (count($missing) == 1)
			{
				$errors[key($missing)] = $this->t('The field %field is required!', [ '%field' => $this->t(current($missing)) ]);
			}
			else
			{
				foreach ($missing as $name => $label)
				{
					$errors[$name] = true;
				}

				$last = array_pop($missing);

				$errors[] = $this->t('The fields %list and %last are required!', [ '%list' => implode(', ', $missing), '%last' => $last ]);
			}
		}
	}

	/*
	 * Validators
	 */

	static public function validate_email(Errors $errors, $element, $value)
	{
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			return true;
		}

		$errors[$element->name] = t('Invalid email address %value for the %label element.', [ 'value' => $value, 'label' => $element->label ]);

		return false;
	}

	static public function validate_url(Errors $errors, $element, $value)
	{
		if (filter_var($value, FILTER_VALIDATE_URL))
		{
			return true;
		}

		$errors[$element->name] = t('Invalid URL %value for the %label element.', [ 'value' => $value, 'label' => $element->label ]);

		return false;
	}

	static public function validate_string(Errors $errors, $element, $value, $rules)
	{
		$messages = [];
		$args = [];

		foreach ($rules as $rule => $params)
		{
			switch ($rule)
			{
				case 'length-min':
				{
					if (strlen($value) < $params)
					{
						$messages[] = t('The string %string is too short (minimum size is :size characters)', [

							'%string' => $value,
							':size' => $params

						]);
					}
				}
				break;

				case 'length-max':
				{
					if (strlen($value) > $params)
					{
						$messages[] = t('The string %string is too long (maximum size is :size characters)', [

							'%string' => shorten($value, 32, 1),
							':size' => $params

						]);
					}
				}
				break;

				case 'regex':
				{
					if (!preg_match($params, $value))
					{
						$messages[] = t('Invalid format of value %value', [ '%value' => $value ]);
					}
				}
				break;
			}
		}

		if ($messages)
		{
			$message = implode('. ', $messages);

			$message .= t(' for the %label input element.', [ '%label' => $element->label ]);

			$errors[$element->name] = t($message, $args);
		}

		return empty($messages);
	}

	static public function validate_range(Errors $errors, $element, $value, $rules)
	{
		list($min, $max) = $rules;

		$rc = ($value >= $min && $value <= $max);

		if (!$rc)
		{
			$errors[$element->name] = t('@wdform.errors.range', [

				'%label' => $element->label,
				':min' => $min,
				':max' => $max

			]);
		}

		return $rc;
	}
}
