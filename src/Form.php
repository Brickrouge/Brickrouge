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

use Brickrouge\Validate\FormValidator;
use Brickrouge\Validate\ErrorRenderer;
use ICanBoogie\ErrorCollection;
use ICanBoogie\ErrorCollectionIterator;

/**
 * A `<FORM>` element.
 *
 * @property FormValidator $validator
 */
class Form extends Element
{
    /**
     * Set to true to disable all the elements of the form.
     */
    public const DISABLED = '#form-disabled';

    /**
     * Used to provide hidden values. Each key/value pair of the array is used to create
     * an hidden input element with key as `name` attribute and value as `value` attribute.
     */
    public const HIDDENS = '#form-hiddens';

    /**
     * Used by elements to define a form label, this is different from the
     * {@link Element::LABEL}, which wraps the element in a `<LABEL>` element, the form label is
     * associated with the element but its layout depend on the form renderer.
     *
     * @deprecated
     *
     * @see Group::LABEL
     */
    public const LABEL = '#group-label';

    /**
     * Complement to the {@link LABEL} tag. Its layout depends on the form renderer.
     */
    public const LABEL_COMPLEMENT = '#form-label-complement';

    /**
     * Values for the elements of the form. The form recursively iterates through its
     * children to set their values, if their values it not already set (e.g. non null).
     */
    public const VALUES = '#form-values';

    /**
     * The class name of the renderer to use to render the children of the form. If no
     * renderer is defined, children are simply concatenated.
     */
    public const RENDERER = '#form-renderer';

    /**
     * Defines the actions of the form.
     *
     * @see render_actions()
     */
    public const ACTIONS = '#form-actions';

    /**
     * Defines form errors.
     */
    public const ERRORS = '#form-errors';

    /**
     * Defines form validator.
     */
    public const VALIDATOR = '#form-validator';

    /**
     * Returns a unique form name.
     *
     * @return string
     */
    protected static function get_auto_name()
    {
        return 'form-autoname-' . self::$auto_name_index++;
    }

    protected static $auto_name_index = 1;

    /**
     * Hidden values, initialized with the {@link HIDDENS} tag.
     *
     * @var array
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
    public function __construct(array $attributes = [])
    {
        $attributes += [

            'action' => isset($attributes['id']) ? '#' . $attributes['id'] : '',
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
            'name' => isset($attributes['id']) ? $attributes['id'] : self::get_auto_name()

        ];

        if (strtoupper($attributes['method']) != 'POST') {
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
        $errors = $this[self::ERRORS] ?: [];

        if ($values || $disabled || count($errors)) {
            if ($values) {
                $values = array_flatten($values);
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

        if ($attribute == self::HIDDENS) {
            $this->hiddens = $value;
        }
    }

    /**
     * Returns a recursive iterator.
     *
     * A recursive iterator is created to traverse the children of the form, with the
     * {@link SELF_FIRST} mode.
     *
     * @return \RecursiveIteratorIterator
     */
    public function getIterator()
    {
        return new \RecursiveIteratorIterator(new RecursiveIterator($this), \RecursiveIteratorIterator::SELF_FIRST);
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
        /* @var $renderer callable */

        $renderer = $this[self::RENDERER];

        if ($renderer) {
            if (is_string($renderer)) {
                $renderer = new $renderer();
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

        if (!$inner_html) {
            $this->add_class('has-no-content');
        } else {
            $this->remove_class('has-no-content');
        }

        #
        # alert message
        #

        $alert = null;
        $errors = $this[self::ERRORS];

        if ($errors) {
            $alert = $this->render_errors(new ErrorCollectionIterator($errors, new ErrorRenderer($this)));
        }

        #
        # actions
        #

        $actions = $this[self::ACTIONS];

        if ($actions) {
            $this->add_class('has-actions');

            $actions = $this->render_actions($actions);
        } else {
            $this->remove_class('has-actions');
        }

        if (!$inner_html && !$actions) {
            throw new ElementIsEmpty();
        }

        return $hiddens . $alert . $inner_html . $actions;
    }

    /**
     * Renders errors as an HTML element.
     *
     * An {@link Alert} object is used to render the provided errors.
     *
     * @param \Traversable|array|string $errors
     *
     * @return string
     */
    protected function render_errors($errors)
    {
        return (new Alert($errors, [

            Alert::CONTEXT => Alert::CONTEXT_DANGER,
            Alert::DISMISSIBLE => true

        ]));
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

        foreach ($hiddens as $name => $value) {
            #
            # we skip undefined values
            #

            if ($value === null) {
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
     * @param \Traversable|array $errors The validation errors.
     */
    protected function alter_elements($values, $disabled, $errors)
    {
        foreach ($this as $element) {
            #
            # disable the element if the form is disabled.
            #

            if ($disabled) {
                $element['disabled'] = true;
            }

            $name = $element['name'];

            if (!$name) {
                continue;
            }

            #
            # if the element is referenced in the errors, we set its state to STATE_DANGER
            #

            if (isset($errors[$name])) {
                $element[Element::STATE] = Element::STATE_DANGER;
            }

            #
            # set value
            #

            if ($values && array_key_exists($name, $values)) {
                $type = $element['type'];
                $value = $values[$name];

                #
                # we don't override the `value` or `checked` attributes if they are already defined
                #

                if ($type == 'checkbox') {
                    if ($element['checked'] === null) {
                        $element['checked'] = !empty($value);
                    }
                } elseif ($type == 'radio') {
                    if ($element['checked'] === null) {
                        $element_value = $element['value'];
                        $element['checked'] = $element_value == $value;
                    }
                } elseif ($element['value'] === null) {
                    $element['value'] = $value;
                }
            }
        }
    }

    /**
     * Validates the form using the provided values.
     *
     * @param array|null $values An array of values to validate, or `null` to validate the values
     * defined by {@link VALUES}.
     * @param ErrorCollection|null $errors
     *
     * @return ErrorCollection
     */
    public function validate(array $values = null, ErrorCollection $errors = null)
    {
        if ($values === null) {
            $values = $this[self::VALUES];
        }

        return $this[self::ERRORS] = $this->resolve_validator()->validate($values, $errors);
    }

    /**
     * Resolves form validator instance.
     *
     * @return FormValidator
     */
    protected function resolve_validator()
    {
        $validator = $this[self::VALIDATOR] ?: $this->validator;

        if (!$validator instanceof FormValidator) {
            throw new \InvalidArgumentException(sprintf(
                "Validator should be an instance of `%s`, `%s` given.",
                FormValidator::class,
                is_object($validator) ? get_class($validator) : gettype($validator)
            ));
        }

        return $validator;
    }
}
