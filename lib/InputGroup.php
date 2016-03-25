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

class InputGroup extends Element
{
	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes);
	}

	protected function render_child($child)
	{
		if ($child instanceof Element)
		{
			return parent::render_child($child);
		}

		return <<<EOT
<span class="input-group-addon">$child</span>
EOT;
	}

	protected function render_class(array $class_names)
	{
		return parent::render_class($class_names + [

			'input-group' => true

		]);
	}
}
