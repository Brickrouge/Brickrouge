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

class Pagination extends Element
{
    public const COUNT = '#pagination-count';
    public const LIMIT = '#pagination-limit';
    public const GAP = '#pagination-gap';
    public const NO_ARROWS = '#pagination-no-arrows';

    /**
     * @deprecated
     */
    public const POSITION = '#pagination-page';
    public const PAGE = '#pagination-page';
    public const SEPARATOR = '#pagination-separator';
    public const URLBASE = '#pagination-urlbase';
    public const USING = '#pagination-using';
    public const WITH = '#pagination-with';
    public const BROWSE_PREVIOUS_LABEL = '#pagination-browse-previous-label';
    public const BROWSE_NEXT_LABEL = '#pagination-browse-next-label';

    public function __construct(array $attributes = [])
    {
        parent::__construct('ul', $attributes + [

                self::LIMIT => 5,
                self::GAP => '<span class="gap"> … </span>',
                self::USING => 'page',

                'class' => 'pagination'

            ]);
    }

    protected $urlbase;

    public function render_inner_html()
    {
        $limit = $this[self::LIMIT];
        $count = $this[self::COUNT];

        $pages = ceil($count / $limit);

        $this->urlbase = $this->render_urlbase();

        $gap = '<li class="disabled">' . $this[self::GAP] . '</li>';
        $separator = $this[self::SEPARATOR];

        $on_page = $this[self::PAGE] + 1;

        $html = '';

        if ($pages > 10) {
            $init_page_max = min($pages, 3);

            for ($i = 1; $i < $init_page_max + 1; $i++) {
                if ($i == $on_page) {
                    $html .= $this->render_current_link($i);
                } else {
                    $html .= $this->render_link($i - 1);
                }

                if ($i < $init_page_max) {
                    $html .= $separator;
                }
            }

            if ($pages > 3) {
                if ($on_page > 1 && $on_page < $pages) {
                    $html .= ($on_page > 5) ? $gap : $separator;

                    $init_page_min = ($on_page > 4) ? $on_page : 5;
                    $init_page_max = ($on_page < $pages - 4) ? $on_page : $pages - 4;

                    for ($i = $init_page_min - 1; $i < $init_page_max + 2; $i++) {
                        $html .= ($i == $on_page) ? $this->render_current_link($i) : $this->render_link($i - 1);

                        if ($i < $init_page_max + 1) {
                            $html .= $separator;
                        }
                    }

                    $html .= ($on_page < $pages - 4) ? $gap : $separator;
                } else {
                    $html .= $gap;
                }

                for ($i = $pages - 2; $i < $pages + 1; $i++) {
                    $html .= ($i == $on_page) ? $this->render_current_link($i) : $this->render_link($i - 1);

                    if ($i < $pages) {
                        $html .= $separator;
                    }
                }
            }
        } else {
            for ($i = 1; $i < $pages + 1; $i++) {
                $html .= ($i == $on_page) ? $this->render_current_link($i) : $this->render_link($i - 1);

                if ($i < $pages) {
                    $html .= $separator;
                }
            }
        }

        if (!$this[self::NO_ARROWS]) {
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

            if ($on_page < $pages) {
                $html .= $this->render_link($on_page, $next_text, 'next');
            } else {
                $html .= '<li class="next disabled"><a href="#">' . $next_text . '</a></li>';
            }

            #
            # add prev (<) link
            #

            if ($on_page > 1) {
                $html = $this->render_link($on_page - 2, $previous_text, 'previous') . $html;
            } else {
                $html = '<li class="previous disabled"><a href="#">' . $previous_text . '</a></li>' . $html;
            }
        }

        return $html;
    }

    public function render_outer_html()
    {
        $limit = $this[self::LIMIT];

        if (!$limit) {
            throw new ElementIsEmpty();
        }

        $count = $this[self::COUNT];
        $pages = ceil($count / $limit);

        if ($pages < 2) {
            throw new ElementIsEmpty();
        }

        return parent::render_outer_html();
    }

    protected function render_urlbase()
    {
        $rc = $this[self::URLBASE];
        $with = $this[self::WITH];

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

        $using = $this[self::USING] ?: 'page';

        unset($parts[$using]);

        $parts[$using] = ''; // so that 'using' is at the end of the string

        #
        # build the query
        #

        return $rc .= '?' . http_build_query($parts, '', '&amp;');
    }

    protected function render_url($n)
    {
        return $this->urlbase . $n;
    }

    protected function render_link($n, $label = null, $class = 'page')
    {
        $rc = '<li' . ($class ? ' class="' . $class . '"' : '') . '><a href="' . $this->render_url($n) . '">';
        $rc .= $label ? $label : ($n + 1);
        $rc .= '</a></li>';

        return $rc;
    }

    protected function render_current_link($n)
    {
        return '<li class="page active"><a href="#">' . $n . '</a></li>';
    }
}
