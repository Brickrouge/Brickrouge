<?php

/*
 * This file is part of the Brickrouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Form;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;

use function assert;
use function is_string;

/**
 * Resolve labels from a form.
 */
class ResolveLabel
{
    /**
     * @var array<string, string>
     */
    private array $labels;

    public function __construct(
        private readonly Form $form
    ) {
    }

    /**
     * @param string $name Element name.
     *
     * @return string|null The Element's label.
     */
    public function __invoke(string $name): ?string
    {
        $labels = $this->obtain_labels();

        return $labels[$name] ?? null;
    }

    /**
     * Obtains labels from form.
     *
     * @return array<string, string>
     *     Where _key_ is an Element's name and _value_ its label.
     */
    private function obtain_labels(): array
    {
        return $this->labels ??= $this->collect_labels();
    }

    /**
     * Collects labels from form.
     *
     * @return array<string, string>
     *     Where _key_ is an Element's name and _value_ its label.
     */
    private function collect_labels(): array
    {
        $labels = [];

        foreach ($this->form as $element) {
            $name = $element['name'];

            if (!$name) {
                continue;
            }

            $label = $element[Element::LABEL_MISSING]
                ?? $element[Group::LABEL]
                    ?? $element[Element::LABEL]
                        ?? $element[Element::LEGEND];

            if (!$label) {
                continue;
            }

            assert(is_string($name));

            $labels[$name] = $label;
        }

        return $labels;
    }
}
