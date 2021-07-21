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

use PHPUnit\Framework\TestCase;

final class AlertTest extends TestCase
{
	private const MESSAGE = 'Something went wrong!';

	/*
	 * with heading
	 */

	/*
	 * undismissible
	 */

	public function test_undismissible_alert()
	{
		$this->assertEquals
		(
			'<div class="alert alert-warning" role="alert">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE)
		);
	}

	public function test_undismissible_alert_error()
	{
		$this->assertEquals
		(
			'<div class="alert alert-danger" role="alert">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, [ Alert::CONTEXT => Alert::CONTEXT_DANGER ])
		);
	}

	public function test_undismissible_alert_info()
	{
		$this->assertEquals
		(
			'<div class="alert alert-info" role="alert">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, [ Alert::CONTEXT => Alert::CONTEXT_INFO ])
		);
	}

	public function test_undismissible_alert_success()
	{
		$this->assertEquals
		(
			'<div class="alert alert-success" role="alert">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, [ Alert::CONTEXT => Alert::CONTEXT_SUCCESS ])
		);
	}
}
