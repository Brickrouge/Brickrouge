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

class Pager extends Element
{
	const T_COUNT = '#pager-count';
	const T_GAP = '#pager-gap';
	const T_LIMIT = '#pager-limit';
	const T_NO_ARROWS = '#pager-no-arrows';
	const T_POSITION = '#pager-position';
	const T_SEPARATOR = '#pager-separator';
	const T_URLBASE = '#pager-urlbase';
	const T_USING = '#pager-using';
	const T_WITH = '#pager-with';

	public function __construct($type, $tags)
	{
		$tags = array_merge
		(
			array
			(
				self::T_LIMIT => 5,
				self::T_SEPARATOR => '<span class="separator">,</span>',
				self::T_GAP => '<span class="gap"> ... </span>',
				self::T_USING => 'page'
			),

			$tags
		);

		parent::__construct($type, $tags);
	}

	protected $urlbase;

	public function render_inner_html()
	{
		$limit = $this->get(self::T_LIMIT);
		$count = $this->get(self::T_COUNT);

		$pages = ceil($count / $limit);

		$this->urlbase = $this->getURLBase();

		$gap = $this->get(self::T_GAP);
		$separator = $this->get(self::T_SEPARATOR);

		#
		#
		#

		// FIXME-20081113: prévoir index par offset

		$on_page = $this->get(self::T_POSITION) + 1;

		$rc = '';

		if ($pages > 10)
		{
			$init_page_max = min($pages, 3);

			for ($i = 1 ; $i < $init_page_max + 1 ; $i++)
			{
				if ($i == $on_page)
				{
					$rc .= $this->getPosition($i);
				}
				else
				{
					$rc .= $this->getLink($i - 1);
				}

				if ($i < $init_page_max)
				{
					$rc .= $separator;
				}
			}

			if ($pages > 3)
			{
				if (($on_page > 1) && ($on_page < $pages))
				{
					$rc .= ($on_page > 5) ? $gap : $separator;

					$init_page_min = ($on_page > 4) ? $on_page : 5;
					$init_page_max = ($on_page < $pages - 4) ? $on_page : $pages - 4;

					for ($i = $init_page_min - 1; $i < $init_page_max + 2; $i++)
					{
						$rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

						if ($i < $init_page_max + 1)
						{
							$rc .= $separator;
						}
					}

					$rc .= ($on_page < $pages - 4) ? $gap : $separator;
				}
				else
				{
					$rc .= $gap;
				}

				for ($i = $pages - 2 ; $i < $pages + 1 ; $i++)
				{
					$rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

					if ($i < $pages)
					{
						$rc .= $separator;
					}
				}
			}
		}
		else
		{
			for ($i = 1 ; $i < $pages + 1 ; $i++)
			{
				$rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

				if ($i < $pages)
				{
					$rc .= $separator;
				}
			}
		}

		if (!$this->get(self::T_NO_ARROWS))
		{
			#
			# add next (>) link
			#

//			if ($this->reverse_arrows ? ($on_page > 1) : ($on_page < $pages))
			if ($on_page < $pages)
			{
				$rc .= $this->getLink($on_page, '<span class="label">' . t('next', array(), array('scope' => array('pager', 'label'), 'default' => 'Next')) . '&nbsp;</span>&gt;', 'next');
//				$rc .= $this->getLink($this->reverse_arrows ? $on_page - 2 : $on_page, '&gt;', 'next');
			}

			#
			# add prev (<) link
			#

//			if ($this->reverse_arrows ? ($on_page < $pages) : ($on_page > 1))
			if ($on_page > 1)
			{
				$rc = $this->getLink($on_page - 2, '&lt;<span class="label">&nbsp;' . t('previous', array(), array('scope' => array('pager', 'label'), 'default' => 'Prev.')) . '</span>', 'previous') . $rc;
//				$rc = $this->getLink($this->reverse_arrows ? $on_page : $on_page - 2, '&lt;', 'previous') . $rc;
			}
		}

		return $rc;
	}

	public function __toString()
	{
		$limit = $this->get(self::T_LIMIT);

		if (!$limit)
		{
			return '';
		}

		$count = $this->get(self::T_COUNT);

		$pages = ceil($count / $limit);

		if ($pages < 2)
		{
			return '';
		}

		return parent::__toString();
	}
	/*
	**

	IMPLEMENTS

	**
	*/

	protected function getURLBase()
	{
		$rc = $this->get(self::T_URLBASE);

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

		$using = $this->get(self::T_USING, 'page');

		unset($parts[$using]);

		$parts[$using] = ''; // so that 'using' is at the end of the string

		#
		# build the query
		#

		$rc .= '?' . http_build_query
		(
			$parts, '', '&amp;'
		);

		return $rc;
	}

	protected function getURL($n)
	{
		return $this->urlbase . $n;
	}

	protected function getLink($n, $label=null, $class=null)
	{
		$rc = '<a href="' . $this->getURL($n) . '"';

		if ($class)
		{
			$rc .= ' class="' . $class . '"';
		}

		$rc .= '>';

		$rc .= $label ? $label : ($n + 1);

		$rc .= '</a>';

		return $rc;
	}

	protected function getPosition($n)
	{
		return '<strong>' . $n . '</strong>';
	}
}