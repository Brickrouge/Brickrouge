<?php

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

echo new Form([

	Form::RENDERER => Form\GroupRenderer::class,

	Form::HIDDENS => [

		'hidden1' => 'one',
		'hidden2' => 'two'

	],

	Form::ACTIONS => [

		new Button('Reset', [ 'type' => 'reset' ]),
		new Button('Submit', [ 'class' => 'primary', 'type' => 'submit' ])

	],

	Element::CHILDREN => [

		'sender_name' => new Text([

			Group::LABEL => "Sender's name",

			Element::REQUIRED => true

		]),

		'sender_email' => new Text([

			Group::LABEL => "Sender's e-mail",

			Element::REQUIRED => true,

		])

	],

	'name' => 'sender'

]);
