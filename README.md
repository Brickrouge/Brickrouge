# Brickrouge [![Build Status](https://secure.travis-ci.org/Brickrouge/Brickrouge.png?branch=master)](http://travis-ci.org/Brickrouge/Brickrouge)

Brickrouge is an object-oriented toolkit for PHP5.3+ that helps you create inputs, widgets,
forms and many other common elements, with all the CSS and JavaScript needed to make them
beautiful and magical.

Here are some of its features:

* Standalone and patchable
* Compatible with Bootstrap
* Localization support
* Fits in a 50ko Phar
* Object-oriented
* Can create any kind of HTML element
* Populate and validate forms

Brickrouge uses [Bootstrap](http://twitter.github.com/bootstrap/) for its style,
and [MooTools](http://mootools.net/) for its magic. Ready under minute,
you'll have everything you need to create beautiful and clean web applications. Together with the
framework [ICanBoogie](http://icanboogie.org/), Brickrouge is one of the
precious components that make the CMS [Icybee](http://icybee.org/).

Please, visit [brickrouge.org](http://brickrouge.org/) for more information.





## Requirements

The package requires PHP 5.3 or later.  
The following packages are also required: [icanboogie/prototype](https://packagist.org/packages/icanboogie/prototype)
and [icanboogie/errors](https://packagist.org/packages/icanboogie/errors).





## Installation

The recommended way to install this package is through [composer](http://getcomposer.org/).
Create a `composer.json` file and run `php composer.phar install` command to install it:

```json
{
	"minimum-stability": "dev",
	"require": {
		"brickrouge/brickrouge": "2.x"
	}
}
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Brickrouge/Brickrouge), its repository can
be cloned with the following command line:

	$ git clone git://github.com/Brickrouge/Brickrouge.git
	




## Documentation

You can generate the documentation for the package and its dependencies with the `make doc`
command. The documentation is generated in the `docs` directory. You can later clean the directory
with the `make clean` command. Note that [ApiGen](http://apigen.org/) is required.





## Testing

The test suite is ran with the `make test` command. [Composer](http://getcomposer.org/) is
automatically installed as well as all dependencies required to run the suite. You can later
clean the directory with the `make clean` command.





## Usage

Brickrouge doesn't need any configuration, simply include the "Brickrouge/bootstrap.php" file
somewhere in your application:

```php
<?php
	
require_once '/path/to/Brickrouge/bootstrap.php';
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

Note: The directory must be writable by PHP.





## Patching Brickrouge

Brickrouge was initially designed to work with the framework
[ICanBoogie](https://github.com/ICanBoogie/ICanBoogie). The project has evolved to
stand alone and now provides means to patch critical features such as translation, errors handling
or form storing/retrieving. Fallback for each feature are provided so you can patch what you need
and leave the rest.

Note: If Brickrouge detects ICanBoogie it will take full advantage of the framework.





### How patching works

Brickrouge helpers are defined in the "lib/helpers.php" file. For the most part they are
dummy functions. For instance, calls to the `Brickrouge\t()` function are forwarded to the
`Brickrouge\Helpers::t()` function, and with some magic the calls can be forwared elsewhere.

Helper functions are patched using the `Brickroue\Helpers::patch()` function.

As a side note, because calls are really handled by the  `Helpers` class, you can either use
`Brickrouge\t()` or `Brickrouge\Helpers::t()`.





### Using ICanBoogie translator

For instance, this is how the `t()` helper function can be patched to use the
translator of the framework [ICanBoogie](https://github.com/ICanBoogie/ICanBoogie):

```php
<?php

Brickrouge\Helpers::patch('t', 'ICanBoogie\I18n\t');
```
	
And this is how the `check_session()` helper function can be patched:

```php
<?php

Brickrouge\Helpers::patch('check_session', function()
{
	return \ICanBoogie\Core::get()->session;
});
```




## Building Brickrouge

Brickrouge comes with pre-built CSS and JavaScript files, compressed and non-compressed, but you
might want to play with its source, or use it as a Phar, in which case you might probably want
to build it yourself. A Makefile is available for this purpose.

Open a terminal, go to its directory and type "make":

	$ cd /path/to/Brickrouge/
	$ make

This consolidates the various CSS and JavaScript files and create compressed and non-compressed
files in the "assets/" directory. Files containing only the differences with Bootstrap
are also created ("-lite-uncompressed.css" and "-lite.css"). The following files are created:

* [brickrouge-lite-uncompressed.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge-lite-uncompressed.css)
* [brickrouge-lite.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge-lite.css)
* [brickrouge-uncompressed.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge-uncompressed.css)
* [brickrouge-uncompressed.js](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge-uncompressed.js)
* [brickrouge.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.css)
* [brickrouge.js](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.js)
* [responsive-uncompressed.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/responsive-uncompressed.css)
* [responsive.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/responsive.css)

Note that you need the [LESS](http://lesscss.org/) compiler to compile the CSS files. JavaScript
files are compressed using the [online UglifyJS JavaScript minification](http://marijnhaverbeke.nl/uglifyjs/).





### Creating a Phar

To create a Phar, type the following commands in a terminal:

	$ cd /path/to/Brickrouge/
	$ make phar

The Phar is created in the parent directory as "Brickrouge.phar".





## More information

For more information and a demonstration please visit the [Brickrouge homepage](http://brickrouge.org/).





## License

Brickrouge is licensed under the New BSD License - See the LICENSE file for details.