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

use Brickrouge\Form;
use ICanBoogie\Error;
use ICanBoogie\ErrorCollection;

/**
 * Render an error into a string using the form's translator.
 */
class ErrorRenderer implements \ICanBoogie\ErrorRenderer
{
    private Form\ResolveLabel $label_resolver;

    public function __construct(
        private readonly Form $form
    ) {
        $this->label_resolver = new Form\ResolveLabel($form);
    }

    /**
     * @inheritdoc
     */
    public function __invoke(Error $error, string $attribute, ErrorCollection $collection): string
    {
        $label = $this->resolve_label($attribute);

        if ($label) {
            $label = $this->translate_label($label);
        }

        return $this->render_error($error->format, $error->args + [
                'name' => $attribute,
                'label' => $label
            ]);
    }

    private function resolve_label(string $attribute): ?string
    {
        return ($this->label_resolver)($attribute);
    }

    /**
     * Renders an error.
     *
     * @param array<int|string, mixed> $args
     */
    private function render_error(string $format, array $args): string
    {
        return $this->t($format, $args, [ 'scope' => 'validation' ]);
    }

    /**
     * Translates a label.
     */
    private function translate_label(string $label): string
    {
        return $this->t($label, [], [ 'scope' => 'element.label' ]);
    }

    /**
     * Translates and formats a string.
     *
     * @param array<int|string, mixed> $args
     * @param array<string, mixed> $options
     */
    private function t(string $format, array $args = [], array $options = []): string
    {
        return $this->form->t($format, $args, $options);
    }
}
