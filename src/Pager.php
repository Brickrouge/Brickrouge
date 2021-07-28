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
 * @deprecated
 */
class Pager extends Element
{
    public const T_COUNT = '#pager-count';
    public const T_GAP = '#pager-gap';
    public const T_LIMIT = '#pager-limit';
    public const T_NO_ARROWS = '#pager-no-arrows';
    public const T_POSITION = '#pager-position';
    public const T_SEPARATOR = '#pager-separator';
    public const T_URLBASE = '#pager-urlbase';
    public const T_USING = '#pager-using';
    public const T_WITH = '#pager-with';
    public const BROWSE_PREVIOUS_LABEL = '#pagination-browse-previous-label';
    public const BROWSE_NEXT_LABEL = '#pagination-browse-next-label';

    public function __construct($type, array $attributes = [])
    {
        parent::__construct($type, $attributes + [

            self::T_LIMIT => 5,
//          self::T_SEPARATOR => '<span class="separator">,</span>',
            self::T_GAP => '<span class="gap"> … </span>',
            self::T_USING => 'page',

            'class' => 'pagination'

        ]);
    }

    protected $urlbase;

    public function render_inner_html(): ?string
    {
        $limit = $this[self::T_LIMIT];
        $count = $this[self::T_COUNT];

        $pages = ceil($count / $limit);

        $this->urlbase = $this->getURLBase();

        $gap = '<li class="disabled">' . $this[self::T_GAP] . '</li>';
        $separator = $this[self::T_SEPARATOR];

        #
        #
        #

        // FIXME-20081113: prévoir index par offset

        $on_page = $this[self::T_POSITION] + 1;

        $rc = '';

        if ($pages > 10) {
            $init_page_max = min($pages, 3);

            for ($i = 1; $i < $init_page_max + 1; $i++) {
                if ($i == $on_page) {
                    $rc .= $this->getPosition($i);
                } else {
                    $rc .= $this->getLink($i - 1);
                }

                if ($i < $init_page_max) {
                    $rc .= $separator;
                }
            }

            if ($pages > 3) {
                if (($on_page > 1) && ($on_page < $pages)) {
                    $rc .= ($on_page > 5) ? $gap : $separator;

                    $init_page_min = ($on_page > 4) ? $on_page : 5;
                    $init_page_max = ($on_page < $pages - 4) ? $on_page : $pages - 4;

                    for ($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
                        $rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

                        if ($i < $init_page_max + 1) {
                            $rc .= $separator;
                        }
                    }

                    $rc .= ($on_page < $pages - 4) ? $gap : $separator;
                } else {
                    $rc .= $gap;
                }

                for ($i = $pages - 2; $i < $pages + 1; $i++) {
                    $rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

                    if ($i < $pages) {
                        $rc .= $separator;
                    }
                }
            }
        } else {
            for ($i = 1; $i < $pages + 1; $i++) {
                $rc .= ($i == $on_page) ? $this->getPosition($i) : $this->getLink($i - 1);

                if ($i < $pages) {
                    $rc .= $separator;
                }
            }
        }

        if (!$this[self::T_NO_ARROWS]) {
            #
            # add next (>) link
            #

            $next_text = $this[self::BROWSE_NEXT_LABEL];
            $previous_text = $this[self::BROWSE_PREVIOUS_LABEL];

            if (!$next_text) {
                $next_text = $this->t('Next', [], [ 'scope' => 'pagination.label', 'default' => 'Next →' ]);
            }

            if (!$previous_text) {
                $previous_text = $this->t('Previous', [], [ 'scope' => 'pagination.label', 'default' => '← Previous' ]);
            }

//          if ($this->reverse_arrows ? ($on_page > 1) : ($on_page < $pages))
            if ($on_page < $pages) {
                $rc .= $this->getLink($on_page, $next_text, 'next');
//              $rc .= $this->getLink($this->reverse_arrows ? $on_page - 2 : $on_page, '&gt;', 'next');
            } else {
                $rc .= '<li class="next disabled"><a href="#">' . $next_text . '</a></li>';
            }


            #
            # add prev (<) link
            #

//          if ($this->reverse_arrows ? ($on_page < $pages) : ($on_page > 1))
            if ($on_page > 1) {
                $rc = $this->getLink($on_page - 2, $previous_text, 'previous') . $rc;
//              $rc = $this->getLink($this->reverse_arrows ? $on_page : $on_page - 2, '&lt;', 'previous') . $rc;
            } else {
                $rc = '<li class="previous disabled"><a href="#">' . $previous_text . '</a></li>' . $rc;
            }
        }

        return '<ul>' . $rc . '</ul>';
    }

    public function __toString()
    {
        $limit = $this[self::T_LIMIT];

        if (!$limit) {
            return '';
        }

        $count = $this[self::T_COUNT];

        $pages = ceil($count / $limit);

        if ($pages < 2) {
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
        $rc = $this[self::T_URLBASE];

        $with = $this[self::T_WITH];

        if ($with) {
            if (is_string($with)) {
                $parts = explode(',', $with);
                $parts = array_map('trim', $parts);
                $parts = array_flip($parts);

                foreach ($parts as $name => &$part) {
                    $part = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
                }
            } else {
                $parts = (array) $with;
            }
        } else {
            $parts = [];
        }

        #
        # add the 'using' part
        #

        $using = $this[self::T_USING] ?: 'page';

        unset($parts[$using]);

        $parts[$using] = ''; // so that 'using' is at the end of the string

        #
        # build the query
        #

        $rc .= '?' . http_build_query(
            $parts,
            '',
            '&amp;'
        );

        return $rc;
    }

    protected function getURL($n)
    {
        return $this->urlbase . $n;
    }

    protected function getLink($n, $label = null, $class = 'page')
    {
        $rc = '<li' . ($class ? ' class="' . $class . '"' : '') . '><a href="' . $this->getURL($n) . '">';
        $rc .= $label ? $label : ($n + 1);
        $rc .= '</a></li>';

        return $rc;
    }

    protected function getPosition($n)
    {
        return '<li class="page active"><a href="#">' . $n . '</a></li>';
    }
}
