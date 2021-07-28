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

use ArrayAccess;
use ICanBoogie\Prototyped;
use InvalidArgumentException;
use IteratorAggregate;
use Stringable;
use Throwable;

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
 * @see http://dev.w3.org/html5/spec/Overview.html#embedding-custom-non-visible-data-with-the-data-attributes
 *
 * @implements ArrayAccess<string, mixed>
 * @implements IteratorAggregate<string, Element>
 */
class Element extends Prototyped implements ArrayAccess, IteratorAggregate, HTMLStringInterface
{
    #
    # special elements
    #

    /**
     * Custom type used to create checkbox elements.
     */
    public const TYPE_CHECKBOX = '#checkbox';

    /**
     * Custom type used to create checkbox group elements.
     */
    public const TYPE_CHECKBOX_GROUP = '#checkbox-group';

    /**
     * Custom type used to create radio elements.
     */
    public const TYPE_RADIO = '#radio';

    /**
     * Custom type used to create radio group elements.
     */
    public const TYPE_RADIO_GROUP = '#radio-group';

    #
    # special attributes
    #

    /**
     * Defines the children of an element.
     */
    public const CHILDREN = '#children';

    /**
     * Defines the default value of an element.
     *
     * The default value is added to the dataset as 'default-value'.
     */
    public const DEFAULT_VALUE = '#default-value';

    /**
     * Defines the description block of an element.
     *
     * @see Element::decorate_with_description()
     */
    public const DESCRIPTION = '#description';

    /**
     * Defines the group of an element.
     */
    public const GROUP = '#group';

    /**
     * Defines the groups that can be used by children elements.
     */
    public const GROUPS = '#groups';

    /**
     * Defines the inline help of an element.
     *
     * @see Element::decorate_with_inline_help()
     */
    public const INLINE_HELP = '#inline-help';

    /**
     * Defines the inner HTML of an element. If the value of the tag is null, the markup will be
     * self-closing.
     */
    public const INNER_HTML = '#inner-html';

    /**
     * Defines the label of an element.
     *
     * @see Element::decorate_with_label()
     */
    public const LABEL = '#label';

    /**
     * Defines the position of the label. Defaults to {@link LABEL_POSITION_AFTER}.
     */
    public const LABEL_POSITION = '#label-position';
    public const LABEL_POSITION_BEFORE = 'before';
    public const LABEL_POSITION_AFTER = 'after';
    public const LABEL_POSITION_ABOVE = 'above';

    /**
     * Defines the label to use to format validation message.
     */
    public const LABEL_MISSING = '#label-missing';

    /**
     * Defines the legend of an element. If the legend is defined the element is wrapped
     * into a fieldset when it is rendered.
     *
     * @see Element::decorate_with_legend()
     */
    public const LEGEND = '#element-legend';

    /**
     * Defines the required state of an element.
     *
     * @see Form::validate()
     * @see http://dev.w3.org/html5/spec/Overview.html#the-required-attribute
     */
    public const REQUIRED = 'required';

    /**
     * Defines the options of the following element types: "select", {@link TYPE_RADIO_GROUP}
     * and {@link TYPE_CHECKBOX_GROUP}.
     */
    public const OPTIONS = '#options';

    /**
     * Defines which options are disabled.
     */
    public const OPTIONS_DISABLED = '#options-disabled';

    /**
     * Defines the state of the element.
     */
    public const STATE = '#state';
    public const STATE_DEFAULT = null;
    public const STATE_DANGER = 'danger';
    public const STATE_SUCCESS = 'success';
    public const STATE_WARNING = 'warning';

    /**
     * Defines the translator used to translate strings.
     */
    public const TRANSLATOR = '#translator';

    /**
     * Defines validation rules.
     */
    public const VALIDATION = '#validation';

    /**
     * Defines the weight of an element. This attribute can be used to reorder children when
     * a parent element is rendered.
     *
     * @see Element::get_ordered_children()
     */
    public const WEIGHT = '#weight';
    public const WEIGHT_BEFORE_PREFIX = 'before:';
    public const WEIGHT_AFTER_PREFIX = 'after:';

    /**
     * Defines the factory name for the widget.
     */
    public const IS = 'brickrouge-is';

    private const INPUTS = [ 'button', 'form', 'input', 'option', 'select', 'textarea' ];
    private const HAS_ATTRIBUTE_DISABLED = [ 'button', 'input', 'optgroup', 'option', 'select', 'textarea' ];
    private const HAS_ATTRIBUTE_VALUE = [ 'button', 'input', 'option' ];
    private const HAS_ATTRIBUTE_REQUIRED = [ 'input', 'select', 'textarea' ];

    /**
     * @var array<class-string, true>
     */
    private static array $handled_assets = [];

    private static function handle_assets(): void
    {
        $class = get_called_class();

        if (isset(self::$handled_assets[$class])) {
            return;
        }

        self::$handled_assets[$class] = true;

        static::add_assets(get_document());
    }

    /**
     * Adds assets to the document.
     */
    protected static function add_assets(Document $document): void
    {
    }

    /**
     * Next available auto element id index.
     */
    private static int $auto_element_id = 1;

    /**
     * Returns a unique element id string.
     */
    public static function auto_element_id(): string
    {
        return 'autoid--' . self::$auto_element_id++;
    }

    /**
     * Type if the element, as provided during {@link __construct()}.
     *
     * @var string
     */
    public string $type;

    /**
     * Tag name of the rendered HTML element.
     *
     * @var string
     */
    public string $tag_name;

    /**
     * An array containing the children of the element.
     *
     * @var array<int|string, Element|string>
     */
    public array $children = [];

    /**
     * Attributes of the element, including HTML and special attributes.
     *
     * @var array<string, mixed>
     */
    private array $attributes = [];

    /**
     * Inner HTML of the element.
     *
     * @see Element::render_inner_html()
     */
    protected ?string $inner_html = null;

    /**
     * @param string $type Type of the element, it can be one of the custom types (`TYPE_*`) or any HTML type.
     *
     * @param array<string, mixed> $attributes HTML and custom attributes.
     */
    public function __construct(string $type, array $attributes = [])
    {
        $this->type = $type;

        #
        # children first
        #

        if (!empty($attributes[self::CHILDREN])) {
            $this->children = [];
            $this->adopt($attributes[self::CHILDREN]);

            unset($attributes[self::CHILDREN]);
        }

        #
        # prepare special elements
        #

        switch ($type) {
            case self::TYPE_CHECKBOX:
            case self::TYPE_RADIO:
                static $translate = [

                    self::TYPE_CHECKBOX => [ 'input', 'checkbox' ],
                    self::TYPE_RADIO => [ 'input', 'radio' ],

                ];

                $this->tag_name = $translate[$type][0];
                $attributes['type'] = $translate[$type][1];
                break;

            case self::TYPE_CHECKBOX_GROUP:
            case self::TYPE_RADIO_GROUP:
                $this->tag_name = 'div';
                break;

            case 'textarea':
                $this->tag_name = 'textarea';

                $attributes += [ 'rows' => 10, 'cols' => 76 ];
                break;

            default:
                $this->tag_name = $type;
        }

        foreach ($attributes as $attribute => $value) {
            $this[$attribute] = $value;
        }

        switch ($this->type) {
            case self::TYPE_CHECKBOX_GROUP:
                $this->add_class('checkbox-group');
                break;

            case self::TYPE_RADIO_GROUP:
                $this->add_class('radio-group');
                break;
        }
    }

    /**
     * Checks is an attribute is set.
     *
     * @param string $offset An attribute.
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Returns the value of an attribute.
     *
     * @param string $offset An attribute.
     *
     * @return mixed The value of the attribute, or `null` if is not set.
     */
    public function offsetGet($offset): mixed
    {
        return $this->attributes[$offset] ?? null;
    }

    /**
     * Sets the value of an attribute.
     *
     * @param string $offset An attribute.
     * @param mixed $value The value of the attribute.
     */
    public function offsetSet($offset, $value)
    {
        switch ($offset) {
            case self::CHILDREN:
                $this->children = [];
                $this->adopt($value);
                break;

            case self::INNER_HTML:
                $this->inner_html = $value;
                break;

            case 'class':
                $this->class = $value ?? '';
                break;

            case 'id':
                unset($this->id);
                break;
        }

        $this->attributes[$offset] = $value;
    }

    /**
     * Removes an attribute.
     *
     * @param string $offset An attribute.
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Iterates through the Element children.
     *
     * @return iterable<int|string, Element>
     */
    public function getIterator(): iterable
    {
        return new Iterator($this);
    }

    private ?Dataset $private_dataset = null;

    /**
     * Returns the {@link Dataset} of the element.
     */
    protected function get_dataset(): Dataset
    {
        return $this->private_dataset ??= new Dataset($this);
    }

    /**
     * Sets the dataset of the element.
     *
     * @param array<string, mixed>|Dataset $dataset
     */
    protected function set_dataset(Dataset|array $dataset): void
    {
        $this->private_dataset = $dataset instanceof Dataset
            ? $dataset
            : new Dataset($this, $dataset);
    }

    /**
     * Returns the attributes of the element.
     *
     * @return array<string, mixed>
     */
    protected function get_attributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the element's id.
     *
     * If the element's id is empty, a unique id is generated and added to its attributes.
     */
    protected function lazy_get_id(): string
    {
        $id = $this['id'];

        if (!$id) {
            $name = $this['name'];

            if ($name) {
                $id = 'autoid--' . normalize($name);
            } else {
                $id = self::auto_element_id();
            }

            $this['id'] = $id;
        }

        return $id;
    }

    /**
     * Class names used to compose the value of the `class` attribute.
     */
    protected array $class_names = [];

    /**
     * Returns the value of the "class" attribute.
     */
    protected function get_class(): string
    {
        $class_names = $this->alter_class_names($this->class_names);

        return $this->render_class($class_names);
    }

    /**
     * Sets the value of the "class" attribute.
     */
    protected function set_class(string $class): void
    {
        $names = explode(' ', trim($class));
        $names = array_map('trim', $names);

        $this->class_names = array_combine($names, array_fill(0, count($names), true));
    }

    /**
     * Adds a class name to the "class" attribute.
     *
     * @return $this
     */
    public function add_class(string $class): self
    {
        $this->class_names[$class] = true;

        return $this;
    }

    /**
     * Removes a class name from the `class` attribute.
     *
     * @return $this
     */
    public function remove_class(string $class): self
    {
        unset($this->class_names[$class]);

        return $this;
    }

    /**
     * Checks if a class name is defined in the `class` attribute.
     */
    public function has_class(string $class_name): bool
    {
        return isset($this->class_names[$class_name]);
    }

    /**
     * Alters the class names.
     *
     * This method is invoked before the class names are rendered.
     *
     * @param array<string, bool|string> $class_names
     *
     * @phpstan-return array<string, bool|string>
     */
    protected function alter_class_names(array $class_names): array
    {
        return $class_names;
    }

    /**
     * Renders the `class` attribute value.
     *
     * @param array<string, bool|string> $class_names
     *     An array of class names. Each key/value pair describe a class name. The key is the identifier of the class
     *     name, the value is its value. If the value is empty then the class name is discarded. If the value is `true`
     *     the identifier of the class name is used as value.
     */
    protected function render_class(array $class_names): string
    {
        $class = '';
        $class_names = array_filter($class_names);

        foreach ($class_names as $name => $value) {
            if ($value === true) {
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
     * @param Element|string|array<string, Element|string> $child The child or children to add.
     */
    public function adopt(Element|string|array $child): void
    {
        if (func_num_args() > 1) {
            $child = func_get_args();
        }

        if (is_array($child)) {
            $children = $child;

            foreach ($children as $name => $child) {
                if (is_numeric($name)) {
                    $this->children[] = $child;
                } else {
                    if ($child instanceof self && $child['name'] === null) {
                        $child['name'] = $name;
                    }

                    $this->children[$name] = $child;
                }
            }
        } else {
            $this->children[] = $child;
        }
    }

    /**
     * Returns the children of the element ordered according to their weight.
     *
     * @return array<int|string, Element>
     */
    public function get_ordered_children(): array
    {
        if (!$this->children) {
            return [];
        }

        return sort_by_weight(
            $this->children,
            fn($v) => $v instanceof self ? ($v[self::WEIGHT] ?? 0) : 0
        );
    }

    /**
     * Returns the HTML representation of a child element.
     */
    protected function render_child(Element|string $child): string
    {
        return (string) $child;
    }

    /**
     * Renders the children of the element into a HTML string.
     *
     * @param array<Element|string> $children
     */
    protected function render_children(array $children): string
    {
        $html = '';

        foreach ($children as $child) {
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
    protected function render_inner_html(): ?string
    {
        $html = match ($this->type) {
            'select' => $this->render_inner_html_for_select(),
            'textarea' => $this->render_inner_html_for_textarea(),
            self::TYPE_CHECKBOX_GROUP => $this->render_inner_html_for_checkbox_group(),
            self::TYPE_RADIO_GROUP => $this->render_inner_html_for_radio_group(),
            default => null,
        };

        $children = $this->get_ordered_children();

        if ($children) {
            $html .= $this->render_children($children);
        } elseif ($this->inner_html !== null) {
            $html = $this->inner_html;
        }

        return $html;
    }

    /**
     * Renders inner HTML of `SELECT` elements.
     */
    protected function render_inner_html_for_select(): string
    {
        #
        # get the name and selected value for our children
        #

        $selected = $this['value'];

        if ($selected === null) {
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

        $options = $this[self::OPTIONS] ?? [];
        $disabled = $this[self::OPTIONS_DISABLED];

        foreach ($options as $value => $label) {
            if ($label instanceof self) {
                $option = $label;
            } else {
                $option = $dummy_option;

                if ($label || is_numeric($label)) {
                    $label = escape($label);
                } else {
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
     */
    protected function render_inner_html_for_textarea(): string
    {
        $value = $this['value'];

        if ($value === null) {
            $value = $this[self::DEFAULT_VALUE];
        }

        return $value ? escape($value) : '';
    }

    /**
     * Renders inner HTML of {@link TYPE_CHECKBOX_GROUP} custom elements.
     */
    protected function render_inner_html_for_checkbox_group(): string
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

        $input = new Element('input', [

            'type' => 'checkbox',
            'readonly' => $readonly,
            'class' => 'form-check-input',

        ]);

        #
        # create the inner content of our element
        #

        $html = '';
        $disabled_list = $this[self::OPTIONS_DISABLED];

        foreach ($this[self::OPTIONS] as $option_name => $label) {
            $input[self::LABEL] = $label;
            $input['name'] = $name ? $name . '[' . $option_name . ']' : $option_name;
            $input['checked'] = !empty($selected[$option_name]);
            $input['disabled'] = $disabled || !empty($disabled_list[$option_name]);
            $input['data-key'] = $option_name;
            $input['data-name'] = $name ?: $option_name;

            $html .= '<div class="form-check">' . $input . '</div>';
        }

        return $html;
    }

    /**
     * Renders inner HTML of {@link TYPE_RADIO_GROUP} custom elements.
     */
    protected function render_inner_html_for_radio_group(): string
    {
        #
        # get the name and selected value for our children
        #

        $name = $this['name'];
        $selected = $this['value'];

        if ($selected === null) {
            $selected = $this[self::DEFAULT_VALUE];
        }

        $disabled = $this['disabled'] ?: false;
        $readonly = $this['readonly'] ?: false;

        #
        # this is the 'template' child
        #

        $input = new Element('input', [

            'type' => 'radio',
            'name' => $name,
            'readonly' => $readonly,
            'class' => 'form-check-input',

        ]);

        #
        # create the inner content of our element
        #
        # add our options as children
        #

        $html = '';
        $disabled_list = $this[self::OPTIONS_DISABLED];

        foreach ($this[self::OPTIONS] as $value => $label) {
            if ($label && !is_object($label) && $label[0] == '.') {
                $label = $this->t(substr($label, 1), [], [ 'scope' => 'element.option' ]);
            }

            $input[self::LABEL] = $label;
            $input['value'] = $value;
            $input['checked'] = (string) $value === (string) $selected;
            $input['disabled'] = $disabled || !empty($disabled_list[$value]);

            $html .= '<div class="form-check">' . $input . '</div>';
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
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed> The altered attributes.
     */
    protected function alter_attributes(array $attributes): array
    {
        $tag_name = $this->tag_name;

        foreach ($attributes as $attribute => $value) {
            if ($attribute === 'value' && !in_array($tag_name, self::HAS_ATTRIBUTE_VALUE)) {
                unset($attributes[$attribute]);

                continue;
            }

            if ($attribute === 'required' && !in_array($tag_name, self::HAS_ATTRIBUTE_REQUIRED)) {
                unset($attributes[$attribute]);

                continue;
            }

            if ($attribute === 'disabled' && !in_array($tag_name, self::HAS_ATTRIBUTE_DISABLED)) {
                unset($attributes[$attribute]);

                continue;
            }

            if ($attribute === 'name' && !in_array($tag_name, self::INPUTS)) {
                unset($attributes[$attribute]);

                continue;
            }

            if ($attribute === 'title' && $value) {
                $attributes[$attribute] = $this->t($value, [], [ 'scope' => 'element.title' ]);
            }
        }

        #
        # value/checked
        #

        if ($this->type === self::TYPE_CHECKBOX && $this['checked'] === null) {
            $attributes['checked'] = !!$this[self::DEFAULT_VALUE];
        } elseif (($tag_name === 'input' || $tag_name === 'button') && $this['value'] === null) {
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
     * @param array<string, mixed> $attributes
     *
     * @throws InvalidArgumentException if the value is an array or an object that doesn't
     * implement the `toString()` method.
     */
    protected function render_attributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $attribute => $value) {
            if ($value === false || $value === null || $attribute[0] === '#') {
                continue;
            } elseif ($value === true) {
                $value = $attribute;
            } elseif (is_array($value) || (is_object($value) && !$value instanceof Stringable)) {
                throw new InvalidArgumentException(format('Invalid value for attribute %attribute: :value', [
                    'attribute' => $attribute,
                    'value' => $value
                ]));
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
     * @param array<string, mixed> $dataset
     *
     * @return array<string, mixed>
     */
    protected function alter_dataset(array $dataset): array
    {
        if (
            (in_array($this->tag_name, self::HAS_ATTRIBUTE_VALUE) || $this->tag_name == 'textarea')
            && $this['data-default-value'] === null
        ) {
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
     * @param array<string, mixed> $dataset
     */
    private function render_dataset(array $dataset): string
    {
        $rc = '';

        foreach ($dataset as $name => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if ($value === null) {
                continue;
            } elseif ($value === false) {
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
     * @throws ElementIsEmpty
     */
    protected function render_outer_html(): string
    {
        $inner = $this->render_inner_html();

        if ($inner === null && $this->tag_name === 'div') {
            throw new ElementIsEmpty();
        }

        $attributes = [];
        $dataset = [];

        foreach ($this->attributes as $attribute => $value) {
            if (str_starts_with($attribute, 'data-')) {
                $dataset[substr($attribute, 5)] = $value;
            } else {
                $attributes[$attribute] = $value;
            }
        }

        $class = $this->class;

        if ($class) {
            $attributes['class'] = $class;
        }

        $html = '<'
            . $this->tag_name
            . $this->render_attributes($this->alter_attributes($attributes))
            . $this->render_dataset($this->alter_dataset($dataset));

        #
        # if the inner HTML of the element is `null`, the element is self closing.
        #

        if ($inner === null) {
            $html .= ' />';
        } else {
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
     */
    protected function decorate(string $html): string
    {
        #
        # add label
        #

        $label = $this[self::LABEL];

        if ($label || $label === '0') {
            $label = $this->t($label, [], [ 'scope' => 'element.label' ]);
            $html = $this->decorate_with_label($html, $label);
        }

        #
        # add inline help
        #

        $help = $this[self::INLINE_HELP];

        if ($help) {
            $help = $this->t($help, [], [ 'scope' => 'element.inline_help' ]);
            $html = $this->decorate_with_inline_help($html, $help);
        }

        #
        # add description
        #

        $description = $this[self::DESCRIPTION];

        if ($description) {
            $description = $this->t($description, [], [ 'scope' => 'element.description' ]);
            $html = $this->decorate_with_description($html, $description);
        }

        #
        # add legend
        #

        $legend = $this[self::LEGEND];

        if ($legend) {
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
     * @param string $label The label as defined by the {@link T_LABEL} tag.
     */
    protected function decorate_with_label(string $html, string $label): string
    {
        $class = 'element-label';

        if ($this[self::REQUIRED]) {
            $class .= ' required';
        }

        if ($this['disabled']) {
            $class .= ' disabled';
        }

        if ($this->has_class('form-check-input')) {
            $class .= ' form-check-label';
        }

        return match ($this[self::LABEL_POSITION]) {
            'above' => <<<EOT
<label class="$class above">$label</label>
$html
EOT,
            'below' => <<<EOT
$html
<label class="$class below">$label</label>
EOT,
            'before' => <<<EOT
<label class="$class wrapping before">$label $html</label>
EOT,
            default => <<<EOT
<label class="$class wrapping after">$html <span class="label-text">$label</span></label>
EOT,
        };
    }

    /**
     * Decorates the specified HTML with a fieldset and the specified legend.
     */
    protected function decorate_with_legend(string $html, string $legend): string
    {
        return '<fieldset><legend>' . $legend . '</legend>' . $html . '</fieldset>';
    }

    /**
     * Decorates the specified HTML with an inline help element.
     */
    protected function decorate_with_inline_help(string $html, string $help): string
    {
        return $html . '<div class="help-inline form-text text-muted">' . $help . '</div>';
    }

    /**
     * Decorates the specified HTML with the specified description.
     */
    protected function decorate_with_description(string $html, string $description): string
    {
        return $html . '<div class="form-text text-muted">' . $description . '</div>';
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
    public function render(): string
    {
        if (get_class($this) !== __CLASS__) {
            static::handle_assets();
        }

        try {
            return $this->decorate(
                $this->render_outer_html()
            );
        } catch (ElementIsEmpty) {
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
        try {
            return $this->render();
        } catch (Throwable $e) {
            return render_exception($e);
        }
    }

    /**
     * Translates and formats a string.
     *
     * The method uses the translator specified by {@link TRANSLATOR} or the {@link t()} function
     * if it is not specified.
     *
     * @param string $pattern The native string to translate.
     * @param array<int|string, mixed> $args An array of replacements to make after the translation. The replacement is
     * handled by the {@link format()} function.
     * @param array<string, mixed> $options An array of additional options, with the following elements:
     * - 'default': The default string to use if the translation failed.
     * - 'scope': The scope of the translation.
     *
     * @return string
     * @see \Brickrouge\t
     */
    public function t(string $pattern, array $args = [], array $options = []): string
    {
        /** @phpstan-var ?callable(string $pattern, array $args = [], array $options = []): string $translator */
        $translator = $this[self::TRANSLATOR];

        return $translator ? $translator($pattern, $args, $options) : t($pattern, $args, $options);
    }
}
