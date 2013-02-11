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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
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
			. Alert::DISSMISS_BUTTON
			. '<h4 class="alert-heading">' . escape($heading) . '</h4>'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::HEADING => $heading))
		);
	}

	/*
	 * undissmisable
	 */

	public function test_undissmisable_alert()
	{
		$this->assertEquals
		(
			'<div class="alert undissmisable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::UNDISSMISABLE => true))
		);
	}

	public function test_undissmisable_alert_error()
	{
		$this->assertEquals
		(
			'<div class="alert alert-error undissmisable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_ERROR, Alert::UNDISSMISABLE => true))
		);
	}

	public function test_undissmisable_alert_info()
	{
		$this->assertEquals
		(
			'<div class="alert alert-info undissmisable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_INFO, Alert::UNDISSMISABLE => true))
		);
	}

	public function test_undissmisable_alert_success()
	{
		$this->assertEquals
		(
			'<div class="alert alert-success undissmisable">'
			. '<div class="content">' . self::MESSAGE . '</div>'
			. '</div>',

			(string) new Alert(self::MESSAGE, array(Alert::CONTEXT => Alert::CONTEXT_SUCCESS, Alert::UNDISSMISABLE => true))
		);
	}
}