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

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use WdArray;

/**
 * This is the base class to create forms.
 *
 * @todo: https://github.com/formasfunction/remotipart
 */
class Form extends Element
{
	const T_DISABLED = '#form-disabled';
	const T_HIDDENS = '#form-hiddens';
	const T_LABEL = '#form-label';
	const T_LABEL_COMPLEMENT = '#form-label-complement';
	const T_NO_LOG = '#form-no-log';
	const T_VALUES = '#form-values';
	const T_RENDERER = '#form-renderer';
	const T_ACTIONS = '#form-actions';

	static protected $auto_name = 1;

	static protected function getAutoName()
	{
		return 'form-autoname-' . self::$auto_name++;
	}

	public $hiddens = array();
	protected $name;

	public function __construct(array $tags)
	{
		#
		# we merge the provided tags with the default tags for the form element
		#

		$tags += array
		(
			'action' => isset($tags['id']) ? '#' . $tags['id'] : '',
			'method' => 'post',
			'enctype' => 'multipart/form-data',
			'name' => isset($tags['id']) ? $tags['id'] : self::getAutoName()
		);

		if ($tags['method'] != 'post')
		{
			unset($tags['enctype']);
		}

		#
		# get form's name from tags
		#

		$this->name = $tags['name'];

		#
		# save hidden
		#

		if (isset($tags[self::T_HIDDENS]))
		{
			$this->hiddens = $tags[self::T_HIDDENS];
		}

		parent::__construct('form', $tags);

		#
		# Add the 'wdform' class to the element
		#

		$this->addClass('wdform');
	}

	/**
	 * Add hidden input elements and log messages to the inner HTML of the element
	 * being converted to a string.
	 *
	 * @see ICanBoogie.Element::render_inner_html()
	 */
	protected function render_inner_html()
	{
		$rc = null;

		//$rc .= '<!-- BEGIN::' . __CLASS__ . '::' . __FUNCTION__ . ' -->';

		#
		# add hidden elements
		#

		foreach ($this->hiddens as $name => $value)
		{
			#
			# we skip undefined values
			#

			if ($value === null)
			{
				continue;
			}

			$rc .= self::makeHidden($name, $value);
		}

		#
		# get the log messages
		#

		if (!$this->get(self::T_NO_LOG))
		{
			global $core;

			$name = $this->name;

			if (!empty($core->session->wdform['logs'][$name]))
			{
				$rc .= '<div class="alert-message error">';
				$rc .= '<a href="#close" class="close">×</a>';

				foreach ($core->session->wdform['logs'][$name] as $definition)
				{
					if ($definition === true)
					{
						continue;
					}

					list($message, $params) = $definition;

					$rc .= '<p>' . t($message, $params) . '</p>';
				}

				$rc .= '</div>';

				#
				# we can now empty the messages
				#

				unset($core->session->wdform['logs'][$name]);
			}
		}

		$renderer = $this->get(self::T_RENDERER);

		if ($renderer)
		{
			if (is_string($renderer))
			{
				$class = 'BrickRouge\Renderer\\' . $renderer;
				$renderer = new $class();
			}

			$content = $renderer($this);
		}
		else
		{
			$content = parent::render_inner_html();
		}

		#

		$actions = $this->get(self::T_ACTIONS);

		if ($actions)
		{
			$content .= $this->render_actions($actions);
		}

		return $rc . $content;
	}

	/**
	 * Renders actions.
	 *
	 * @param boolean|array|string $actions Actions can be defined as a boolean, an array or a
	 * string.
	 *
	 * If $actions is defined as `true` a submit button with the 'primary' classe is used
	 * instead.
	 *
	 * If $actions is an array it is imploded with the `<span class="separator">&nbsp;</span>`
	 * glue.
	 *
	 * If $actions is a string it is used as is.
	 *
	 * @return string return $actions stringified and wrapped in a `div.actions` element.
	 */
	protected function render_actions($actions)
	{
		if (is_array($actions))
		{
			$actions = implode('<span class="separator">&nbsp;</span>', $actions);
		}
		else if ($actions === true)
		{
			$actions = new Button
			(
				'Send', array
				(
					'type' => 'submit',
					'class' => 'primary'
				)
			);
		}

		return '<div class="actions">' . $actions . '</div>';
	}

	/**
	 *
	 * Walk through children and modify their value and "disable" attribute according to
	 * the T_VALUES and T_DISABLED tags.
	 *
	 * @see ICanBoogie.Element::__toString()
	 */
	public function __toString()
	{
		#
		# walk children to set their values or disable them
		#

		$values = $this->get(self::T_VALUES);
		$disabled = $this->get(self::T_DISABLED);

		if ($values || $disabled)
		{
			if ($values)
			{
				$values = WdArray::flatten($values);
			}

			$this->walk(array($this, 'tweakElement_callback'), array($values, $disabled), 'name');
		}

		return parent::__toString();
	}

	public function isElementMissing($name)
	{
		global $core;

		return isset($core->session->wdform['logs'][$this->name][$name]);
	}

	protected function tweakElement_callback($element, $userdata, $name)
	{
		list($values, $disabled) = $userdata;

		#
		# if the element is referenced in the error log, we had the class 'missing error'
		#

		if ($this->isElementMissing($name))
		{
			$element->addClass('missing');
			$element->addClass('error');
		}

		#
		# set values
		#

		if ($values)
		{
			if (array_key_exists($name, $values))
			{
				$type = $element->get('type');
				$value = $values[$name];

				#
				# we don't override the `value` or `checked` attributes if they are already defined
				#

				if (($type == 'checkbox') || ($type == 'radio'))
				{
					if ($element->get('checked') === null)
					{
						$element->set('checked', !empty($value));
					}
				}
				else if ($element->get('value') === null)
				{
					$element->set('value', $value);
				}
			}
		}

		#
		# If the form is disabled, all of its input elements should be disabled too.
		#

		if ($disabled)
		{
			$element->set('disabled', true);
		}
	}

	/**
	 * Return a string defining an hidden input element.
	 * @param $name
	 * @param $value
	 * @return string The HTML representation of the hidden input element.
	 */

	static public function makeHidden($name, $value)
	{
		if (is_array($value))
		{
			$rc = '';
			$name .= '[]';

			foreach ($value as $v)
			{
				$rc .= self::makeHidden($name, $v) . PHP_EOL;
			}

			return $rc;
		}

		return '<input type="hidden" name="' . $name . '" value="' . wd_entities($value) . '" />';
	}

	/*
	**

	SAVE & RESTORE

	**
	*/

	const T_KEY = '#form-key';
	const SAVED_LIMIT = 10;

	/**
	 * Save the form in the session for future validation.
	 *
	 * @return string The MD5 key used to identify the form.
	 */

	public function save()
	{
		global $core;

		#
		# before we save anything, we might want to do some cleanup. in order to avoid sessions
		# filled with forms, we only maintain a few. The limit is set using the SAVED_LIMIT constant.
		# If the number of forms saved in session is bigger than this limit, the older forms are removed.
		#

		if (isset($core->session->wdform['saved']))
		{
			if (1)
			{
				$n = count($core->session->wdform['saved']);
			}
			else
			{
				$n = 0;
				$size = 0;

				foreach ($core->session->wdform['saved'] as $serialized)
				{
					$n++;
					$size += strlen($serialized);
				}

				wd_log('already \1 forms in session, using \2 ko', $n, round($size / 1024, 2));
			}

			if ($n > self::SAVED_LIMIT)
			{
				$core->session->wdform['saved'] = array_slice($core->session->wdform['saved'], $n - self::SAVED_LIMIT);
			}
		}

		#
		# we create a unique key for our form
		#

		$key = md5(uniqid(mt_rand(), true));

		#
		# in order to be able to recognize our form later, we add the key
		# as a hidden input element
		#

		$this->hiddens[self::T_KEY] = $key;

		#
		# now we can serialize our form and save it in the user's session
		#

		try
		{
			$core->session->wdform['saved'][$key] = serialize($this);
		}
		catch (\PDOException $e)
		{
			throw new Exception('Unable to serialize form because of PDO SHIT: \1', array($this));
		}

//		wd_log('saved: \1', wd_entities($$core->session->wdform['saved'][$key]));

		return $this;
	}

	/**
	 * Load a form previously saved for validation.
	 *
	 * @param $key The key used to identify the form to load, or an array in which
	 * the T_KEY tag defines the key.
	 *
	 * @return object A BrickRouge\Form object
	 */
	static public function load($key)
	{
		if (is_array($key))
		{
			if (empty($key[self::T_KEY]))
			{
				wd_log_error('Missing form\'s key to retrieve form');

				return false;
			}

			$key = $key[self::T_KEY];
		}

		if (self::exists($key))
		{
			global $core;

			$form = unserialize($core->session->wdform['saved'][$key]);

			unset($core->session->wdform['saved'][$key]);

			$form->set(self::T_VALIDATOR, $form->validator);

			return $form;
		}
		else
		{
			wd_log_error('The form has expired');
		}

		return false;
	}

	/**
	 * Checks if a previously saved form exists for a given key.
	 *
	 * @param $key The key used to identify the form.
	 *
	 * @return boolean Return TRUE if the form exists.
	 */

	static public function exists($key)
	{
		global $core;

		return !empty($core->session->wdform['saved'][$key]);
	}

	protected $mandatories = array();
	protected $validators = array();
	protected $validator = null;

	/**
	 * Export only the necessary variables for future validation.
	 *
	 * There are two arrays exported : 'mandatories' and 'validators', as well as the validator
	 * for the form itself and its name.
	 *
	 * 'mandatories' is an array of identifier/label pairs.
	 *
	 * 'validators' is an array of identifier/element pairs.
	 *
	 * @return array
	 */

	public function __sleep()
	{
		#
		# mandatories and validators
		#

		$this->walk(array($this, 'exportElements_callback'), null, 'name');

		#
		# form's validator
		#

		$this->validator = $this->get(self::T_VALIDATOR);

		#
		# we return the variable to serialize, we only export variables needed
		# for later validation.
		#

		return array('name', 'validator', 'mandatories', 'validators');
	}

	static public function selectElementLabel($element)
	{
		$label = $element->get(self::T_LABEL_MISSING);

		if (!$label)
		{
			$label = $element->get(Form::T_LABEL);
		}

		if (!$label)
		{
			$label = $element->get(Element::T_LABEL);
		}

		if (!$label)
		{
			$label = $element->get(self::T_LEGEND, $label);
		}

		#
		# remove HTML markups from the label
		#

		$label = self::translate_label($label);
		$label = strip_tags($label);

		return $label;
	}

	protected function exportElements_callback($element, $userdata, $name)
	{
		#
		# we don't include the validator for the form itself, as it will cause
		# some serious infinite loop trouble during validation. The 'validator' variable
		# is used instead.
		#

		if ($element == $this)
		{
			return;
		}

		if ($element->get(self::T_REQUIRED))
		{
			$this->mandatories[$name] = self::selectElementLabel($element);
		}

		//if ($element->get(self::T_VALIDATOR))
		{
			$this->validators[$name] = $element;
		}
	}


	/*
	**

	VALIDATION

	**
	*/

	public function validate($values)
	{
		#
		# validation without prior save
		#

		if (empty($values[self::T_KEY]))
		{
			$this->__sleep();
		}

		#
		#
		#

		$er = false;

		#
		# we flatten the array so that we can easily get values
		# for keys such as `cars[1][color]`
		#

		$values = WdArray::flatten($values);

		$this->values = $values;

		#
		# process required values
		#

		$validators = $this->validators;

		foreach ($validators as $identifier => $element)
		{
			$element->form = $this;
			$element->name = $identifier;
			$element->label = self::selectElementLabel($element);
		}

		#
		# process required elements
		#

		$missing = array();

		foreach ($this->mandatories as $name => $label)
		{
			if (!isset($values[$name]) || (isset($values[$name]) && is_string($values[$name]) && !strlen(trim($values[$name]))))
			{
				$missing[$name] = t($label);

				$er = true;

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
				$this->logMissing(key($missing), array_shift($missing));
			}
			else
			{
				global $core;

				foreach ($missing as $name => $label)
				{
					$core->session->wdform['logs'][$this->name][$name] = true;
				}

				$last = array_pop($missing);

				$this->log(null, 'The fields %list and %last are required!', array('%list' => implode(', ', $missing), '%last' => $last));
			}
		}

		#
		# process elements validators
		#
		# note: If the value for the element is `null` and the value is not required the element's
		# validator is *not* called.
		#

	    foreach ($validators as $name => $element)
	    {
	    	$value = isset($values[$name]) ? $values[$name] : null;

	    	if ($value === null && empty($this->mandatories[$name]))
	    	{
	    		continue;
	    	}

	    	if (!$element->validate($value))
	    	{
	    		$er = true;
	    	}
	    }

		if ($er)
		{
			return;
		}

		return parent::validate($values);
	}

	public function log($identifier, $message, array $args=array())
	{
		global $core;

		$name = $this->name;
		$session = &$core->session->wdform;

		if ($identifier)
		{
			#
			# we don't overwrite messages
			#

			if (!empty($session['logs'][$name][$identifier]))
			{
				return;
			}

			$session['logs'][$name][$identifier] = array($message, $args);
		}
		else
		{
			$session['logs'][$name][] = array($message, $args);
		}
	}

	public function logMissing($identifier, $label)
	{
		$this->log($identifier, 'The field %field is required!', array('%field' => t($label)));
	}

	/**
	 * Fetches the log of the form.
	 *
	 * @return array
	 */
	public function fetch_log()
	{
		global $core;

		$name = $this->name;
		$session = $core->session;

		if (empty($session->wdform['logs'][$name]))
		{
			return;
		}

		$log = array();

		foreach ($session->wdform['logs'][$name] as $identifier => $args)
		{
			$log[$identifier] = $args === true ? '' : call_user_func_array('t', $args);
		}

		unset($session->wdform['logs'][$name]);

		return $log;
	}

	/*
	**

	VALIDATORS

	**
	*/

	static public function validate_email($element, $value)
	{
		if (filter_var($value, FILTER_VALIDATE_EMAIL))
		{
			return true;
		}

		$element->form->log($element->name, '@wdform.errors.email', array('%value' => $value, '%label' => $element->label));

		return false;
	}

	static public function validate_spam($element, $value)
	{
		global $core;

		if ($core->user->is_guest)
		{
			$score = wd_spamScore($value, null, null);

			if ($score < 1)
			{
				$element->form->log($element->name, '@wdform.errors.spam', array('%score' => $score));

				return false;
			}
		}

		return true;
	}

	static public function validate_string($element, $value, $rules)
	{
		$messages = array();
		$args = array();

		foreach ($rules as $rule => $params)
		{
			switch ($rule)
			{
				case 'minlength':
				{
					if (strlen($value) < $params)
					{
						$messages[] = t
						(
							'The string %string is too short (minimum size is :size characters)', array
							(
								'%string' => $value,
								':size' => $params
							)
						);
					}
				}
				break;

				case 'regex':
				{
					if (!preg_match($params, $value))
					{
						$messages[] = t
						(
							'Invalid format of value %value', array('%value' => $value)
						);
					}
				}
				break;
			}
		}

		if ($messages)
		{
			$message = implode('. ', $messages);

			$message .= t(' for the %label input element', array('%label' => $element->label));

			$element->form->log($element->name, $message, $args);
		}

		return !$messages;
	}

	static public function validate_range($element, $value, $rules)
	{
		list($min, $max) = $rules;

		$rc = ($value >= $min && $value <= $max);

		if (!$rc)
		{
			$element->form->log
			(
				$element->name, '@wdform.errors.range', array
				(
					'%label' => $element->label,
					':min' => $min,
					':max' => $max
				)
			);
		}

		return $rc;
	}

	/**
	 * Tries to load the form associated with the operation.
	 *
	 * This function is a callback for the `ICanBoogie\Operation::get_form` event.
	 *
	 * @param Event $event
	 * @param Operation $operation
	 */
	public static function on_operation_get_form(Event $event, Operation $operation)
	{
		$params = $event->params;

		if (empty($params[self::T_KEY]))
		{
			return;
		}

		$form = self::load($params);

		if ($form)
		{
			$event->rc = $form;
			$event->stop();
		}
	}
}