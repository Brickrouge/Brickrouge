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
	/**
	 * @var Form
	 */
	private $form;

	/**
	 * @var Form\ResolveLabel
	 */
	private $label_resolver;

	/**
	 * @param Form $form
	 */
	public function __construct(Form $form)
	{
		$this->form = $form;
		$this->label_resolver = new Form\ResolveLabel($form);
	}

	/**
	 * @inheritdoc
	 */
	public function __invoke(Error $error, $attribute, ErrorCollection $collection)
	{
		$label = $this->resolve_label($attribute);

		if ($label)
		{
			$label = $this->translate_label($label);
		}

		return $this->render_error($error->format, $error->args + [

			'name' => $attribute,
			'label' => $label

		]);
	}

	/**
	 * Resolves label.
	 *
	 * @param string $attribute
	 *
	 * @return string
	 */
	protected function resolve_label($attribute)
	{
		$label_resolver = $this->label_resolver;

		return $label_resolver($attribute);
	}

	/**
	 * Renders an error.
	 *
	 * @param string $format
	 * @param array $args
	 *
	 * @return string
	 */
	protected function render_error($format, $args)
	{
		return $this->t($format, $args, [ 'scope' => 'validation' ]);
	}

	/**
	 * Translates a label.
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	protected function translate_label($label)
	{
		return $this->t($label, [], [ 'scope' => 'element.label' ]);
	}

	/**
	 * Translates and formats a string.
	 *
	 * @param string $format
	 * @param array $args
	 * @param array $options
	 *
	 * @return string
	 */
	protected function t($format, array $args = [], array $options = [])
	{
		return $this->form->t($format, $args, $options);
	}
}
