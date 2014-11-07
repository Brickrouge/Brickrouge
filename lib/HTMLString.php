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

/**
 * Representation of an HTML string.
 *
 * An HTML string is considered safe to use and is not escaped after it has been rendered.
 */
class HTMLString implements HTMLStringInterface
{
	protected $html;

	public function __construct($html)
	{
		$this->html = $html;
	}

	public function render()
	{
		return $this->html;
	}

	public function __toString()
	{
		try
		{
			return (string) $this->render();
		}
		catch (\Exception $e)
		{
			return render_exception($e);
		}
	}
}
