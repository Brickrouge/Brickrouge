Brickrouge
==========

Brickrouge is an object-oriented toolkit for PHP5.3+ that helps you create inputs, widgets,
forms and many other common elements, with all the CSS and JavaScript needed to make them
beautiful and magical.

Here are some of its features:

* Standalone and patchable
* Compatible with Bootstrap
* Supports localization
* Fits in a 50ko Phar
* Object-oritented
* Can create any kind of HTML element
* Populate and validate forms

Brickrouge uses [Bootstrap](http://twitter.github.com/bootstrap/) from
twitter for its style, and [MooTools](http://mootools.net/) for its magic. Ready under minute,
you'll have everything you need to create beautiful and clean web applications. Together with the
framework [ICanBoogie](http://icanboogie.org/), Brickrouge is one of the
precious components that make the CMS [Icybee](http://icybee.org/).

Homepage: <http://brickrouge.org/>  
Author: Olivier Laviale [@olvlvl](https://twitter.com/olvlvl) – [http://weirdog.com/](http://www.weirdog.com/)




Usage
-----

Brickrouge doesn't need any configuration, simply include the "Brickrouge/startup.php" file
somewhere in your application:

```php
<?php
	
require_once '/path/to/Brickrouge/startup.php';
```
	
Or, if you use it as a Phar:

```php
<?php
	
require_once '/path/to/Brickrouge.phar';
```



### Using Brickrouge's autoloader

Brickrouge provides a simple autoloader that can be used to load its classes:

```php
<?php

require_once '/path/to/Brickrouge.phar';

Brickrouge\register_autoloader();
```

	
	
### Making files accessible

Brickrouge can make unaccessible files–such as assets in the Phar–accessible from the web by
copying them to a directory defined by the `Brickrouge\ACCESSIBLE_ASSETS` constant :

```php
<?php

define('Brickrouge\ACCESSIBLE_ASSETS', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

require_once '/path/to/Brickrouge.phar';

Brickrouge\regsiter_autoloader();

?>

<link rel="stylesheet" href="<?= Brickrouge\Document::resolve_url(Brickrouge\ASSETS . 'brickrouge.css') ?>" type="text/css">
<link rel="stylesheet" href="<?= Brickrouge\Document::resolve_url(Brickrouge\ASSETS . 'responsive.css') ?>" type="text/css">
```

Note: The directory mush be writtable by PHP.




Patching Brickrouge
-------------------

Brickrouge was initially designed to work with the framework
[ICanBoogie](https://github.com/ICanBoogie/ICanBoogie). The project has evolved to
stand alone and now provides means to patch critical features such as translation, errors handling
or form storing/retrieving. Fallbacks for each feature are provided so you can patch what you need
and leave the rest.

Note: If Brickrouge detects ICanBoogie it will take full advantage of the framework.




### How patching works

Brickrouge uses helpers defined in the "lib/helpers.php" file. These are for the most part dummy
functions which call callbacks. For instance the `Brickrouge\t()` function calls the
`Brickrouge\Patchable::$callback_translate` callback, which defaults to the
`Brickrouge\Patchable::fallback_translate()` function.

Thus, in order to patch the `t()` helper you need to overwrite the `$callback_translate`
static property.




### Patching with the ICanBoogie framework

If you take a look at the "startup.php" file you'll notice how helpers are patched
if the [ICanBoogie](https://github.com/ICanBoogie/ICanBoogie) framework is available.

For instance, this is how the `t()` helper function is patched:

```php
<?php

Patchable::$callback_translate = 'ICanBoogie\I18n::translate';
```
	
And this is how the `check_session()` helper function is patched:

```php
<?php

Patchable::$callback_check_session = function()
{
	return \ICanBoogie\Core::get()->session;
};
```




Building Brickrouge
-------------------

Brickrouge comes with pre-built CSS and JavaScript files, compressed and non-compressed, but you
might want to play with its source, or use it as a Phar, in which case you might probably want
to build it yourself. A Makefile is available for this purpose.

Open a terminal, go to its directory and type "make":

	$ cd /path/to/Brickrouge/
	$ make

This consolidates the various CSS and JavaScript files and create compressed and non-compressed
files in the "assets/" directory. Files containing only the differences with Bootstrap
are also created ("-lite-uncompressed.css" and "-lite.css"). The following files are created:

* [brickrouge-lite-uncompressed.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge-lite-uncompressed.css)
* [brickrouge-lite.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge-lite.css)
* [brickrouge-uncompressed.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge-uncompressed.css)
* [brickrouge-uncompressed.js](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge-uncompressed.js)
* [brickrouge.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge.css)
* [brickrouge.js](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/brickrouge.js)
* [responsive-uncompressed.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/responsive-uncompressed.css)
* [responsive.css](https://github.com/ICanBoogie/Brickrouge/blob/master/assets/responsive.css)

Note that you need the [LESS](http://lesscss.org/) compiler to compile the CSS files. JavaScript
files are compressed using the [online UglifyJS JavaScript minification](http://marijnhaverbeke.nl/uglifyjs/).




### Creating a Phar

To create a Phar, type the following commands in a terminal:

	$ cd /path/to/Brickrouge/
	$ make phar

The Phar is created in the parent directory as "Brickrouge.phar".




New BSD Licence
---------------

Copyright (c) 2011-2012, Olivier Laviale.  
All rights reserved.

Redistribution and use in source and binary forms, with or without modification,
are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice,
  this list of conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice,
  this list of conditions and the following disclaimer in the documentation
  and/or other materials provided with the distribution.

* Neither the name of Olivier Laviale nor the names of its
  contributors may be used to endorse or promote products derived from this
  software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.




More information
----------------

For more information and a demonstration please visit the [Brickrouge homepage](http://brickrouge.org/).