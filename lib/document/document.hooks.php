<?php

namespace BrickRouge\Document;

use BrickRouge;
use ICanBoogie\Event;

class Hooks
{
	public static function markup_document_title(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$document = $core->document;

		$title = $document->title;

		Event::fire('render_title:before', array('title' => &$title), $document);

		$rc = '<title>' . BrickRouge\escape($title) . '</title>';

		Event::fire('render_title', array('rc' => &$rc), $document);

		return $rc;
	}

	static public function markup_document_metas(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$document = $core->document;

		$http_equiv = array
		(
			'Content-Type' => 'text/html; charset=' . WDCORE_CHARSET
		);

		$metas = array
		(

		);

		Event::fire('render_metas:before', array('http_equiv' => &$http_equiv, 'metas' => &$metas), $document);

		$rc = '';

		foreach ($http_equiv as $name => $content)
		{
			$rc .= '<meta http-equiv="' . BrickRouge\escape($name) . '" content="' . BrickRouge\escape($content) . '" />' . PHP_EOL;
		}

		foreach ($metas as $name => $content)
		{
			$rc .= '<meta name="' . BrickRouge\escape($name) . '" content="' . BrickRouge\escape($content) . '" />' . PHP_EOL;
		}

		Event::fire('render_metas', array('rc' => &$rc), $document);

		return $rc;
	}
}