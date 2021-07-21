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

/**
 * Resolve labels from a form.
 */
class ResolveLabel
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @var array
     */
    private $labels;

    /**
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @param string $name Element name.
     *
     * @return string|null
     */
    public function __invoke($name)
    {
        $labels = $this->obtain_labels();

        return isset($labels[$name]) ? $labels[$name] : null;
    }

    /**
     * Obtains labels from form.
     *
     * @return array
     */
    protected function obtain_labels()
    {
        $labels = &$this->labels;

        if ($labels === null) {
            $labels = $this->collect_labels();
        }

        return $labels;
    }

    /**
     * Collects labels from form.
     *
     * @return array
     */
    protected function collect_labels()
    {
        $labels = [];

        /* @var $element Element */

        foreach ($this->form as $element) {
            $name = $element['name'];

            if (!$name) {
                continue;
            }

            $label = $element[Element::LABEL_MISSING]
                ?: $element[Group::LABEL]
                    ?: $element[Element::LABEL]
                        ?: $element[Element::LEGEND];

            if (!$label) {
                continue;
            }

            $labels[$name] = $label;
        }

        return $labels;
    }
}
