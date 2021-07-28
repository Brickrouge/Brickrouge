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

use ICanBoogie\ErrorCollection;
use Stringable;

use function is_iterable;

/**
 * A `<DIV.alert>` element.
 */
class Alert extends Element
{
    /**
     * The context of the alert, one of `CONTEXT_*`.
     */
    public const CONTEXT = '#alert-context';
    public const CONTEXT_DANGER = 'danger';
    public const CONTEXT_INFO = 'info';
    public const CONTEXT_SUCCESS = 'success';
    public const CONTEXT_WARNING = 'warning';

    /**
     * The heading of the alert.
     */
    public const HEADING = '#alert-heading';

    /**
     * Set to `true` for dismissible alerts.
     */
    public const DISMISSIBLE = '#alert-dismissible';

    /**
     * Alert message(s).
     *
     * @phpstan-var string|iterable<string>|HTMLStringInterface
     */
    private string|iterable|HTMLStringInterface $message;

    /**
     * Creates a `<DIV.alert>` element.
     *
     * @param iterable<string>|string|HTMLStringInterface $message
     *     The alert message is provided as a string, an array of strings or a {@link Errors} object.
     *
     *     If the message is provided as a string it is used as is. If the message is provided as an array each value
     *     of the array is considered as a message. If the message is provided as an {@link Errors} object each entry
     *     of the object is considered as a message.
     *
     *     Each message is wrapped in a `<P>` element, and they are concatenated to create the final message.
     *
     * @inheritDoc
     */
    public function __construct(iterable|string|HTMLStringInterface $message, array $attributes = [])
    {
        $this->message = $message;

        parent::__construct('div', $attributes + [

                self::CONTEXT => $message instanceof ErrorCollection ? self::CONTEXT_DANGER : self::CONTEXT_WARNING,

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
    protected function alter_class_names(array $class_names): array
    {
        $class_names = parent::alter_class_names($class_names);

        $context = $this[self::CONTEXT];

        if ($context) {
            $class_names['alert-' . $context] = true;
        }

        if ($this[self::DISMISSIBLE]) {
            $class_names['alert-dismissible'] = true;
        }

        return $class_names;
    }

    /**
     * @throws ElementIsEmpty if the message is empty.
     *
     * @inheritdoc
     */
    public function render_inner_html(): ?string
    {
        $message = $this->message;

        if (!$message) {
            throw new ElementIsEmpty();
        }

        return
            $this->render_alert_dismiss($this[self::DISMISSIBLE] ?? false) .
            $this->render_alert_heading($this[self::HEADING]) .
            $this->render_alert_content($this->render_alert_message($message));
    }

    /**
     * Renders dismiss button.
     */
    private function render_alert_dismiss(bool $dismissible): ?string
    {
        if (!$dismissible) {
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
     */
    private function render_alert_heading(?string $heading): ?string
    {
        if (!$heading) {
            return null;
        }

        return '<h4 class="alert-heading">' . escape($heading) . '</h4>';
    }

    /**
     * Renders alert message.
     *
     * @phpstan-param string|iterable<string>|HTMLStringInterface $message
     */
    private function render_alert_message(string|iterable|HTMLStringInterface $message): string
    {
        if ($message instanceof HTMLStringInterface) {
            return $message;
        }

        if (is_iterable($message)) {
            return $this->render_errors($message);
        }

        return $message;
    }

    /**
     * Renders alert content.
     *
     * @throws ElementIsEmpty
     */
    protected function render_alert_content(string $message): string
    {
        if (!$message) {
            throw new ElementIsEmpty();
        }

        return <<<EOT
        <div class="content">$message</div>
        EOT;
    }

    /**
     * Renders errors as an HTML string.
     *
     * @param iterable<Stringable|string> $errors
     */
    private function render_errors(iterable $errors): string
    {
        $message = '';

        foreach ($errors as $error) {
            if ((string) $error === '') {
                continue;
            }

            $message .= '<p>' . $error . '</p>';
        }

        return $message;
    }
}
