<?php

/*
 * This file is part of the BrickRouge package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Element;

use BrickRouge\Element;

class Ranger extends Element
{
	const T_START = '#ranger-start';
	const T_LIMIT = '#ranger-limit';
	const T_COUNT = '#ranger-count';
	const T_WITH = '#ranger-with';
	const T_EDITABLE = '#ranger-editable';
	const T_NO_ARROWS = '#ranger-no-arrows';

	public function __construct($type, $tags)
	{
		parent::__construct($type, $tags);

		$this->addClass('wdranger');
	}

	protected function render_inner_html()
	{
		$start = max(1, $this->get(self::T_START));
		$limit = $this->get(self::T_LIMIT, 10);
		$count = $this->get(self::T_COUNT);

		$start_final = $start;

		if ($this->get(self::T_EDITABLE) && $count > $limit)
		{
			$start_final = (string) new Element
			(
				self::E_TEXT, array
				(
					'name' => 'start',
					'value' => $start,
					'size' => 4
				)
			);
		}

		$rc = t
		(
			'From :start to :finish on :max', array
			(
				':start' => $start_final,
				':finish' => $start + $limit > $count ? $count : $start + $limit - 1,
				':max' => $count
			),

			array('scope' => 'ranger.element')
		);

		if ($count > $limit && !$this->get(self::T_NO_ARROWS))
		{
			$url = $this->getURLBase();

			$rc .= '<a href="' . $url . ($start - $limit < 1 ? $count - $limit + 1 + ($count % $limit ? $limit - ($count % $limit) : 0) : $start - $limit) . '" class="browse previous">&lt;</a>';
			$rc .= '<a href="' . $url . ($start + $limit >= $count ? 1 : $start + $limit) . '" class="browse next">&gt;</a>';
		}

		return $rc;
	}

	protected function getURLBase()
	{
		$with = $this->get(self::T_WITH);

		if ($with)
		{
			if (is_string($with))
			{
				$parts = explode(',', $with);
				$parts = array_map('trim', $parts);
				$parts = array_flip($parts);

				foreach ($parts as $name => &$part)
				{
					$part = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
				}
			}
			else
			{
				$parts = (array) $with;
			}
		}
		else
		{
			$parts = array();
		}

		#
		# add the 'using' part
		#

		$using = 'start';//$this->get(self::T_USING, 'start');

		unset($parts[$using]);

		$parts['limit'] = $this->get(self::T_LIMIT, 10);
		$parts[$using] = ''; // so that 'using' is at the end of the string

		#
		# build the query
		#

		$rc = '';//$this->get(self::T_URLBASE);

		$rc .= '?' . http_build_query
		(
			$parts, '', '&amp;'
		);

		return $rc;
	}
}