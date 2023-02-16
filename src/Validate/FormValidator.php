<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Validate;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Validate\FormValidator\ValidateValues;
use ICanBoogie\ErrorCollection;

use function Brickrouge\array_flatten;
use function is_string;

/**
 * Validates a form.
 */
class FormValidator
{
    /**
     * @var ValidateValues|callable|null
     */
    private $validate_values;

    public function __construct(
        private readonly Form $form,
        ValidateValues|callable $validation = null
    ) {
        $this->validate_values = $validation;
    }

    /**
     * Validate values.
     *
     * @param array<string, mixed> $values
     */
    public function validate(array $values, ErrorCollection $errors = null): ErrorCollection
    {
        #
        # we flatten the array so that we can easily get values
        # for keys such as `cars[1][color]`
        #

        $values = array_flatten($values);
        $errors = $this->ensure_error_collection($errors);

        #

        $elements = $this->collect_elements();

        $required = $this->filter_required_elements($elements);
        $this->validate_required($required, $values, $errors);

        $rules = $this->collect_rules($elements, $required, $values, $errors);
        $this->validate_values($values, $rules, $errors);

        return $errors;
    }

    /**
     * Creates an {@link ErrorCollection} instance if none is provided.
     */
    private function ensure_error_collection(ErrorCollection $errors = null): ErrorCollection
    {
        return $errors ?? new ErrorCollection();
    }

    /**
     * Collect required or with validation elements.
     *
     * @return Element[]
     */
    private function collect_elements(): array
    {
        $elements = [];

        foreach ($this->form as $element) {
            if ($element[Element::REQUIRED] || $element[Element::VALIDATION]) {
                $elements[] = $element;
            }
        }

        return $elements;
    }

    /**
     * Filter required elements.
     *
     * @param Element[] $elements
     *
     * @return array<string, Element>
     */
    private function filter_required_elements(array $elements): array
    {
        $required = [];

        foreach ($elements as $element) {
            if ($element[Element::REQUIRED]) {
                $name = $element['name'];
                assert(is_string($name));
                $required[$name] = $element;
            }
        }

        return $required;
    }

    /**
     * Validates required elements.
     *
     * @param array<string, Element> $required
     *     Where _key_ if an Element's name and _value_ an Element.
     * @param array<string, mixed> $values
     * @param ErrorCollection $errors
     */
    private function validate_required(array $required, array $values, ErrorCollection $errors): void
    {
        $missing = [];

        foreach ($required as $name => $element) {
            if (
                !isset($values[$name])
                || (is_string($values[$name]) && !strlen(trim($values[$name])))
            ) {
                $missing[$name] = $this->resolve_label($element);
            }
        }

        if (!$missing) {
            return;
        }

        if (count($missing) == 1) {
            $errors->add(key($missing), "The field %field is required!", [

                '%field' => current($missing)

            ]);

            return;
        }

        foreach ($missing as $name => $label) {
            $errors->add($name, true);
        }

        $last = array_pop($missing);

        $errors->add_generic("The fields %list and %last are required!", [

            '%list' => implode(', ', $missing),
            '%last' => $last

        ]);
    }

    /**
     * Collect validation rules from elements.
     *
     * @param Element[] $elements
     * @param array<string, Element> $required
     *     Where _key_ if an Element's name and _value_ an Element.
     * @param array<string, mixed> $values
     * @param ErrorCollection $errors
     *
     * @return array<string, mixed>
     */
    private function collect_rules(array $elements, array $required, array $values, ErrorCollection $errors): array
    {
        $rules = [];

        foreach ($elements as $element) {
            $name = $element['name'];

            assert(is_string($name));

            if (!$element[Element::VALIDATION] || isset($errors[$name])) {
                continue;
            }

            $value = $values[$name] ?? null;

            if (($value === null || $value === '') && empty($required[$name])) {
                continue;
            }

            $rules[$name] = $element[Element::VALIDATION];
        }

        return $rules;
    }

    protected function resolve_label(Element $element): ?string
    {
        $label = $element[Element::LABEL_MISSING]
            ?: $element[Group::LABEL]
                ?: $element[Element::LABEL]
                    ?: $element[Element::LEGEND]
                        ?: null;

        if (!$label) {
            return null;
        }

        #
        # remove HTML markups from the label
        #

        $label = $this->form->t($label, [], [ 'scope' => 'element.label' ]);
        $label = strip_tags($label);

        return $label;
    }

    /**
     * Validate values against a set of rules.
     *
     * @param array $values
     * @param array $rules
     * @param ErrorCollection $errors
     */
    protected function validate_values(array $values, array $rules, ErrorCollection $errors)
    {
        $validate_values = $this->validate_values;
        $validate_values($values, $rules, $errors);
    }
}
