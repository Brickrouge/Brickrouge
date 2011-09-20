<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation::get_form' => 'BrickRouge\Form::on_operation_get_form',
	),

	'objects.methods' => array
	(
		'ICanBoogie\Core::__get_document' => 'BrickRouge\Document::hook_get_document'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'BrickRouge\Document\Hooks::markup_document_metas', array()
		),

		'document:title' => array
		(
			'BrickRouge\Document\Hooks::markup_document_title', array()
		)
	)
);