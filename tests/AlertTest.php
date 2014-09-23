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

class AlertTest extends \PHPUnit_Framework_TestCase
{
	const HEADING = 'Oops…';
	const MESSAGE = 'Something went wrong!';

	public function test_alert()
	{
		$this->assertEquals
		(
			'<div class="alert">'
			. Alert::DISMISS_BUTTON
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE)
		);
	}

	public function test_alert_error()
	{
		$this->assertEquals
		(
			'<div class="alert alert-error">'
			. Alert::DISMISS_BUTTON
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_ERROR))
		);
	}

	public function test_alert_info()
	{
		$this->assertEquals
		(
			'<div class="alert alert-info">'
			. Alert::DISMISS_BUTTON
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_INFO))
		);
	}

	public function test_alert_success()
	{
		$this->assertEquals
		(
			'<div class="alert alert-success">'
			. Alert::DISMISS_BUTTON
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_SUCCESS))
		);
	}

	/*
	 * with heading
	 */

	public function test_alert_with_heading()
	{
		$this->assertEquals
		(
			'<div class="alert alert-block">'
			. Alert::DISMISS_BUTTON
			. '<h4 class="alert-heading">' . self::HEADING . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::HEADING => self::HEADING))
		);
	}

	public function test_alert_error_with_heading()
	{
		$this->assertEquals
		(
			'<div class="alert alert-error alert-block">'
			. Alert::DISMISS_BUTTON
			. '<h4 class="alert-heading">' . self::HEADING . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_ERROR, Alert::HEADING => self::HEADING))
		);
	}

	public function test_alert_info_with_heading()
	{
		$this->assertEquals
		(
			'<div class="alert alert-info alert-block">'
			. Alert::DISMISS_BUTTON
			. '<h4 class="alert-heading">' . self::HEADING . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_INFO, Alert::HEADING => self::HEADING))
		);
	}

	public function test_alert_success_with_heading()
	{
		$this->assertEquals
		(
			'<div class="alert alert-success alert-block">'
			. Alert::DISMISS_BUTTON
			. '<h4 class="alert-heading">' . self::HEADING . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_SUCCESS, Alert::HEADING => self::HEADING))
		);
	}

	public function test_heading_is_escaped()
	{
		$heading = '<"Oops…">';

		$this->assertEquals
		(
			'<div class="alert alert-block">'
			. Alert::DISMISS_BUTTON
			. '<h4 class="alert-heading">' . escape($heading) . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::HEADING => $heading))
		);
	}

	/*
	 * undismissable
	 */

	public function test_undismissable_alert()
	{
		$this->assertEquals
		(
			'<div class="alert undismissable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::UNDISMISSABLE => true))
		);
	}

	public function test_undismissable_alert_error()
	{
		$this->assertEquals
		(
			'<div class="alert alert-error undismissable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_ERROR, Alert::UNDISMISSABLE => true))
		);
	}

	public function test_undismissable_alert_info()
	{
		$this->assertEquals
		(
			'<div class="alert alert-info undismissable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_INFO, Alert::UNDISMISSABLE => true))
		);
	}

	public function test_undismissable_alert_success()
	{
		$this->assertEquals
		(
			'<div class="alert alert-success undismissable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_SUCCESS, Alert::UNDISMISSABLE => true))
		);
	}
}