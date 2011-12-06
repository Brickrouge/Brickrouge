BrickRouge
==========

BrickRouge is an open source object-oriented toolkit for PHP5.3+ that helps you create elements,
widgets and forms for your webapps or sites. Using the many features provided by the toolkit you
can create any kind of element, or you can use built-in elements which are commoly found in web
applications such as text inputs or date pickers. Whatever you choose you can
always override attributes or behaviours to get exactly what you want.

BrickRouge supports localization and provides hooks to patch critical features.

Out of the box you have everything you need to create anchors, buttons, text inputs, textareas,
selects, checkbox group, radio groups, pagers, rangers, saluation pickers, alert messages, groups,
widgets and forms.

BrickRouge is compatible with
[Bootstrap](http://twitter.github.com/bootstrap/) from twitter and
[MooTools](http://mootools.net). It's ready in a minute
with all the CSS and javascript you need to create beautiful and clean webapps.

*Website*: <http://brickrouge.org>  
*Author*: Olivier Laviale <olivier.laviale@gmail.com>


Usage
-----

BrickRouge doesn't need any configuration simply include the "BrickRouge.php" file somewhere
in your application:

	<?php
	
	require_once '/path/to/BrickRouge/BrickRouge.php';
	
Or if your are using it as a Phar:

	<?php
	
	require_once '/path/to/BrickRouge.phar/BrickRouge.php';


### Using BrickRouge's autoloader

BrickRouge provides a simple autoloader that can be used to load its own classes. You need to
define the `BrickRouge\AUTOLOAD` constant to enable it:

	<?php
	
	define('BrickRouge\AUTOLOAD', true);
	
	require_once '/path/to/BrickRouge/BrickRouge.php';





Patching BrickRouge
-------------------

BrickRouge was initially designed for the
[ICanBoogie](https://github.com/ICanBoogie/ICanBoogie) framework, the project evolved to
stand alone and provides means to patch critical features such as translation or
session starting. Fallbacks for each feature are provided so you can patch what you need
and leave the rest.

Note: If BrickRouge detects ICanBoogie it will take full advantage of the framework.


### How it works

BrickRouge uses helpers defined in the "/lib/helpers.php" file. These are for the most part dummy
functions which call callbacks. For example the `BrickRouge\t()` function calls the
`BrickRouge\Patchable::$callback_translate` callback, which defaults to the
`BrickRouge\Patchable::fallback_translate()` function.

Thus, in order to patch the `t()` helper you just need to overwrite the `$callback_translate`
property.  


### Example with the ICanBoogie framework

If you take a look at the "BrickRouge.php" file you'll notice how BrickRouge patches its helpers if
it detects the [ICanBoogie](https://github.com/ICanBoogie/ICanBoogie) framework.

This is how it patches its `t()` helper:

	Patchable::$callback_translate = 'ICanBoogie\I18n::translate';
	
And this is how it patches its `check_session()` helper:

	Patchable::$callback_check_session = function()
	{
		global $core;

		return $core->session;
	};




Building BrickRouge
-------------------

BrickRouge comes with pre-built CSS and JS files, compressed and non-compressed, but you might
want to play with its source, or use it as a Phar, in which case you might probably want to build
it yourself. A Makefile is available for this purpose. Open a terminal, go to its directory and
type "make":

	$ cd /path/to/BrickRouge/
	$ make

This consolidates the various CSS and JS files and create compressed and non-compressed
files in the "/BrickRouge/assets/" directory.

Note that you need the [LESS](http://lesscss.org/) compiler as well as the
[YUI compressor](http://developer.yahoo.com/yui/compressor/) installed.


### Creating a Phar

To create a Phar simply type:

	$ cd /path/to/BrickRouge/
	$ make phar

The Phar is created in the parent directory under "BrickRouge.phar".




More information
----------------

For more information please visit the [BrickRouge website](http://brickrouge.org/).




Licence
-------

BrickRouge is licenced under the BSD licence.