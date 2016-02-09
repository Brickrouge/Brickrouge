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
 * A `<DIV.alert>` element.
 */
class Alert extends Element
{
	/**
	 * The context of the alert, one of `CONTEXT_*`.
	 */
	const CONTEXT = '#alert-context';
	const CONTEXT_DANGER = 'danger';
	const CONTEXT_INFO = 'info';
	const CONTEXT_SUCCESS = 'success';
	const CONTEXT_WARNING = 'warning';

	/**
	 * The heading of the alert.
	 */
	const HEADING = '#alert-heading';

	/**
	 * Set to `true` for dismissible alerts.
	 */
	const DISMISSIBLE = '#alert-dismissible';

	/**
	 * Alert message.
	 */
	protected $message;

	/**
	 * Creates a `<DIV.alert>` element.
	 *
	 * @param string|array|Errors $message The alert message is provided as a string,
	 * an array of strings or a {@link Errors} object.
	 *
	 * If the message is provided as a string it is used as is. If the message is provided as an
	 * array each value of the array is considered as a message. If the message is provided as
	 * an {@link Errors} object each entry of the object is considered as a message.
	 *
	 * Each message is wrapped in a `<P>` element and they are concatenated to create the final
	 * message.
	 *
	 * If the message is an instance of {@link Errors} the {@link CONTEXT} attribute is
	 * set to {@link CONTEXT_DANGER} in the initial attributes.
	 *
	 * @param array $attributes Additional attributes.
	 */
	public function __construct($message, array $attributes = [])
	{
		$this->message = $message;

		parent::__construct('div', $attributes + [

			self::CONTEXT => $message instanceof Errors ? self::CONTEXT_DANGER : self::CONTEXT_WARNING,

			'class' => 'alert',
			'role' => 'alert'

		]);
	}

	/**
	 * Adds the `alert-danger`, `alert-info`, or `alert-success` class names according to the
	 * {@link CONTEXT} attribute.
	 *
	 * Adds the `alert-dismissible` class name if the {@link DISMISSIBLE} attribute is true.
	 *
	 * @inheritdoc
	 */
	protected function alter_class_names(array $class_names)
	{
		$class_names = parent::alter_class_names($class_names);

		$context = $this[self::CONTEXT];

		if ($context)
		{
			$class_names['alert-' . $context] = true;
		}

		if ($this[self::DISMISSIBLE])
		{
			$class_names['alert-dismissible'] = true;
		}

		return $class_names;
	}

	/**
	 * @throws ElementIsEmpty if the message is empty.
	 *
	 * @inheritdoc
	 */
	public function render_inner_html()
	{
		$message = $this->message;

		if (!$message)
		{
			throw new ElementIsEmpty;
		}

		return
			$this->render_alert_dismiss($this[self::DISMISSIBLE]) .
			$this->render_alert_heading($this[self::HEADING]) .
			$this->render_alert_content($this->render_alert_message($message));
	}

	/**
	 * Renders dismiss button.
	 *
	 * @param bool $dismissible
	 *
	 * @return string|null
	 */
	protected function render_alert_dismiss($dismissible)
	{
		if (!$dismissible)
		{
			return null;
		}

		$aria_label = escape($this->t("Close", [], [ 'scope' => 'alert' ]));

		return <<<EOT
<button type="button" class="close" data-dismiss="alert" aria-label="$aria_label">
    <span aria-hidden="true">&times;</span>
</button>
EOT;
	}

	/**
	 * Renders alert heading.
	 *
	 * @param string|null $heading
	 *
	 * @return string|null
	 */
	protected function render_alert_heading($heading)
	{
		if (!$heading)
		{
			return null;
		}

		return '<h4 class="alert-heading">' . escape($heading) . '</h4>';
	}

	/**
	 * Renders alert message.
	 *
	 * @param mixed $message
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException if the message cannot be rendered.
	 */
	protected function render_alert_message($message)
	{
		if (is_array($message) || $message instanceof \Traversable)
		{
			return $this->render_errors($message);
		}
		else if (is_object($message))
		{
			throw new \InvalidArgumentException("Don't know how to render message of type: " . get_class($message));
		}

		return $message;
	}

	/**
	 * Renders alert content.
	 *
	 * @param string $message
	 *
	 * @return string
	 */
	protected function render_alert_content($message)
	{
		return <<<EOT
<div class="content">$message</div>
EOT;
	}

	/**
	 * Renders errors as an HTML string.
	 *
	 * @param Errors $errors
	 *
	 * @return string
	 */
	protected function render_errors(Errors $errors)
	{
		$message = '';

		foreach ($errors as $error)
		{
			if ($error === '')
			{
				continue;
			}

			$message .= '<p>' . $error . '</p>';
		}

		return $message;
	}
}
