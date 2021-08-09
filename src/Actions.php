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

use function implode;
use function is_array;
use function is_string;

/**
 * An actions element that may be used with forms and popovers.
 */
class Actions extends Element
{
    public const ACTIONS_BOOLEAN = 'boolean';

    /**
     * Actions.
     *
     * @phpstan-var self::ACTIONS_*|string|array<string|int, string|Element>|true
     */
    private string|array|bool $actions;

    /**
     * @phpstan-param self::ACTIONS_*|string|array<string|int, string|Element>|true $actions
     *     Actions can be defined as a bool, an array a string or an instance of the {@link Element} class.
     *
     * @see {@link render_inner_html()}
     */
    public function __construct(string|array|bool $actions, array $attributes = [])
    {
        $this->actions = $actions;

        parent::__construct('div', $attributes + [ 'class' => 'actions' ]);
    }

    /**
     * Renders the actions.
     *
     * If actions are defined as the string "boolean" they are replaced by an array with the
     * buttons `button[data-action="cancel"].btn-secondary` and
     * `button[data-action="ok"][type=submit].btn-primary`.
     *
     * If actions are defined as a boolean, they are replaced by a
     * `button[type=submit][data-action="ok"].btn-primary` element with the label "Send".
     *
     * If actions are defined as an array, the array is concatenated with the glue
     * `<span class="separator">&nbsp;</span>`.
     *
     * Otherwise actions are used as is.
     */
    protected function render_inner_html(): ?string
    {
        return parent::render_inner_html() . $this->render_actions($this->actions);
    }

    /**
     * Renders actions.
     *
     * @phpstan-param self::ACTIONS_*|string|array<string|int, string|Element>|true $actions
     */
    protected function render_actions(mixed $actions): string
    {
        if ($actions === self::ACTIONS_BOOLEAN) {
            $actions = [

                new Button("Cancel", [ 'data-action' => 'cancel', 'class' => 'btn-secondary' ]),
                new Button("Ok", [ 'data-action' => 'ok', 'class' => 'btn-primary', 'type' => 'submit' ]),

            ];
        } elseif ($actions === true) {
            return (string) new Button("Ok", [ 'data-action' => 'ok', 'class' => 'btn-primary', 'type' => 'submit' ]);
        }

        if (is_array($actions)) {
            foreach ($actions as $name => $action) {
                if (!is_string($name) || !$action instanceof Element || $action['name'] !== null) {
                    continue;
                }

                $action['name'] = $name;
            }

            return implode($actions);
        }

        return $actions;
    }
}
