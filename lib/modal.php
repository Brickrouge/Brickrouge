<?php

namespace Brickrouge;

class Modal extends Element
{
	const ACTIONS = '#modal-actions';

	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	protected function alter_class_names(array $class_names)
	{
		return parent::alter_class_names($class_names) + array
		(
			'modal' => true,
			'hide' => true,
			'fade' => true
		);
	}

	protected function render_inner_html()
	{
		$html = '';

		$header = $this->render_modal_header();

		if ($header)
		{
			$header = '<div class="modal-title">' . $header . '</div>';
		}

		$html .= <<<EOT
<div class="modal-header">
	<button class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
	{$header}
</div>
EOT;

		$body = $this->render_modal_body();

		if ($body === null)
		{
			throw new ElementIsEmpty();
		}

		$html .= <<<EOT
<div class="modal-body">
	{$body}
</div>
EOT;

		$footer = $this->render_modal_footer();

		if ($footer instanceof Actions)
		{
			$html .= $footer->add_class('modal-footer');
		}
		else if ($footer)
		{

			$html .= <<<EOT
<div class="modal-footer">
	{$footer}
</div>
EOT;
		}

		return <<<EOT
<div class="modal-dialog">$html</div>
EOT;
	}

	protected function decorate_with_legend($html, $legend)
	{
		return $html;
	}

	protected function render_modal_header()
	{
		return $this[self::LEGEND];
	}

	protected function render_modal_body()
	{
		return parent::render_inner_html();
	}

	protected function render_modal_footer()
	{
		$actions = $this[self::ACTIONS];

		if ($actions && !($actions instanceof Actions))
		{
			$actions = new Actions($actions);
		}

		return $actions;
	}
}
