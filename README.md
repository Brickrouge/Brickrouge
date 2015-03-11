# Brickrouge

[![Release](https://img.shields.io/packagist/v/Brickrouge/Brickrouge.svg)](https://github.com/Brickrouge/Brickrouge/releases)
[![Build Status](https://img.shields.io/travis/Brickrouge/Brickrouge.svg)](http://travis-ci.org/Brickrouge/Brickrouge)
[![HHVM](https://img.shields.io/hhvm/brickrouge/brickrouge.svg)](http://hhvm.h4cc.de/package/brickrouge/brickrouge)
[![Code Quality](https://img.shields.io/scrutinizer/g/Brickrouge/Brickrouge.svg)](https://scrutinizer-ci.com/g/Brickrouge/Brickrouge)
[![Code Coverage](https://img.shields.io/coveralls/Brickrouge/Brickrouge.svg)](https://coveralls.io/r/Brickrouge/Brickrouge)
[![Packagist](https://img.shields.io/packagist/dt/brickrouge/brickrouge.svg)](https://packagist.org/packages/brickrouge/brickrouge)

Brickrouge helps you create HTML elements and custom HTML elements such as inputs, forms,
dropdowns, popover, calendars… with all the CSS and JavaScript required to make them beautiful
and magical.

Here are some of its features:

* Create any kind of HTML element as well as custom HTML elements.
* Compatible with Bootstrap
* Standalone and patchable
* Object-oriented
* Localization support
* Populate and validate forms

Brickrouge uses [Bootstrap](http://twitter.github.com/bootstrap/) for its style,
and [MooTools](http://mootools.net/) for its magic. Ready under a minute,
you'll have everything you need to create beautiful and clean web applications. Together with the
framework [ICanBoogie](http://icanboogie.org/), Brickrouge is one of the
precious components that make the CMS [Icybee](http://icybee.org/).

Please, visit [brickrouge.org](http://brickrouge.org/) for more information.





## Creating elements

With the [Element][] class you can create any kind of HTML element. The attributes are defined
using an array and custom attributes are used to define custom properties.

```php
<?php

use Brickrouge\Element;

echo new Element('div', [

	'data-type' => 'magic',

	'class' => 'well'

]);
```

```html
<div class="well" data-type="magic" />
```





### Specifying the content of an element

The content of an element, or inner HTML, is specified by either `INNER_HTML` or `CHILDREN`
custom attributes. `INNER_HTML` specifies the inner HTML of an element, while `CHILDREN`
specifies the children of an element.

```php
<?php

use Brickrouge\Element;

echo new Element('div', [

	Element::INNER_HTML => "I'm in a (magic) well",

	'data-type' => 'magic',

	'class' => 'well'

]);
```

```html
<div class="well" data-type="magic">I'm in a (magic) well</div>
```

Note that `CHILDREN` always wins over `INNER_HTML`:

```php
<?php

use Brickrouge\Element;

echo new Element('div', [

	Element::INNER_HTML => "I'm in a (magic) well",
	Element::CHILDREN => [

		'<span>Me too !</span>',

		new Element('span', [ Element::INNER_HTML => "Me three !" ])

	],

	'data-type' => 'magic',

	'class' => 'well'

]);
```

```html
<div class="well" data-type="magic"><span>Me too !</span><span>Me three !</span></div>
```





## Specific classes

Altought any HTML element can be created with the [Element][] class, specific classes are
available for specific element types. They usually help in creating complex elements.
Here is a list of the classes included with Brickrouge:

- [A][]: A link element.
- [Actions][]: An actions group that can be used by forms, popovers, dialogs…
- [Alert][]: An alert element.
- [Button][]: A button element.
- [Form][]: A form.
- [Group][]: A control group element, usually used in forms to group controls.
- [Modal][]: A modal.
- [Popover][]: A popover.
- [Searchbox][]: A searchbox, as found in navigation bars.
- [Text][]: A text input.





## Widgets

Brickrouge's widgets are what is generally called [custom HTML elements](https://www.google.com/search?q=html+custome+elements).
Widget types are associated with a JavaScript constructor and Brickrouge makes sure that widgets
are constructed when the DOM is ready or updated, or when the `widget` custom property of an
element is obtained.

The following is an example of a very simple widget, when it is constructed its background is set
to the color defined by the data attribute `color`:

```php
<?php

use Brickrouge\Element;

echo new Element('div', [

	Element::IS => "Color",
	Element::INNER_HTML => "Color!",

	'data-color' => "#F0F",
	'data-color-name' => "Fuchsia"

	'id' => 'my-color'

]);
```

HTML representation of the element:

```html
<div brickrouge-is="Color" id="my-color" data-color="#F0F" data-color-name="Fuchsia">Color!</div>
```

The widget constructor always takes as arguments the element for which the widget is constructed
and the normalized data attributes of that element.

```js
Brickrouge.Widget.Color = new Class({

	initialize: function(el, options)
	{
		el = document.id(el)
		el.setStyle('background', options.color)
		el.innerHTML = options.colorName
	}

});
```





### Obtaining the widget associated with an element

The `widget` custom property is used to obtain the widget associated with an element (if any). If
the widget has not yet been created, getting the property creates it. The element
associated with a widget is always available through its `element` property.

```js
var color = document.id('my-color').get('widget')
, element = color.element
```





### When a widget is constructed

When a widget is constructed the `brickrouge.widget` event is fired on the window object.

```js
window.addEvent('brickrouge.widget', function(widget) {

	console.log('A widget has just been constructed:', widget)

})
```





### When the document is updated

The `brickrouge.update` event is fired when the `Brickrouge.updateDocument()` method is invoked.
The event is fired just before widgets are constructed.

```js
window.addEvent('brickrouge.update', function(fragment) {

	console.log('The document was updated by the following element:', fargement)

})
```




### Constructing widgets _en masse_

Widgets are first created when the `domready` event is fired. Later, if the document is updated
with possibly new widgets, the `Brickrouge.updateDocument()` is used to construct the new widgets.

```js
// considering that `fragment` contains the new elements that were added to the DOM

Brickrouge.updateDocument(fragment)
```





## Forms

Forms are usually instances of the [Form][] class. The children of the form are specified using
the `CHILDREN` custom attribute, while the actions of the form are specified using `ACTIONS`.
For convenience, hidden values can be specified using `HIDDEN`.

```php
<?php

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Group;
use Brickrouge\Text;

echo new Form([

	Form::RENDERER => 'Group',

	Form::HIDDENS => [

		'hidden1' => 'one',
		'hidden2' => 'two'

	],

	Form::ACTIONS => [

		new Button('Reset', [ 'type' => 'reset' ]),
		new Button('Submit', [ 'class' => 'primary', 'type' => 'submit' ])

	],

	Form::CHILDREN => [

		'sender_name' => new Text([

			Group::LABEL => "Sender's name",

			Element::REQUIRED => true

		]),

		'sender_email' => new Text([

			Group::LABEL => "Sender's e-mail",

			Element::REQUIRED => true,
			Element::VALIDATOR => [ 'Brickrouge\Form::validate_email' ]

		])

	],

	'name' => 'sender'

]);
```

The produced HTML, formatted for readability:

```html
<form name="sender" action="" method="POST" enctype="multipart/form-data" class="has-actions">
	<input type="hidden" name="hidden1" value="one" />
	<input type="hidden" name="hidden2" value="two" />

	<fieldset class="group--primary group no-legend">
		<div class="control-group control-group--sender-name required">
			<label for="autoid--sender-name" class="controls-label">Sender's name</label>
			<div class="controls">
				<input required="required" type="text" name="sender_name" id="autoid--sender-name" />
			</div>
		</div>

		<div class="control-group control-group--sender-email required">
			<label for="autoid--sender-email" class="controls-label">Sender's e-mail</label>
			<div class="controls">
				<input required="required" type="text" name="sender_email" id="autoid--sender-email" />
			</div>
		</div>
	</fieldset>

	<div class="form-actions">
		<button type="reset" class="btn">Reset</button>
		<span class="separator">&nbsp;</span>
		<button class="primary btn" type="submit">Submit</button>
	</div>
</form>
```





### Submitting forms using XHR

Forms can be sent using XHR very easily thanks to the JavaScript `Form` class:

```js
var form = new Brickrouge.Form('myForm', {

	onComplete: function(response) {

		console.log('complete:', response)

	}

});

form.submit()
```

[ICanBoogie][] operation responses are supported and the following properties are recognized:

- `errors`: An array of key/value where _key_ is the name of an element, and _value_ the error
message for that element.
- `message`: If the request is successful, this property is used as a _success_ message.

The class automatically creates alerts according to this properties. If the `replaceOnSuccess`
option is true the _success_ message is inserted before the form element, which is then hidden.





### Retriving the instance associated with a form element

The `Form` instance associated with a form element can be retrived with the `retrieve()` method:

```js
var form = document.id('myForm').retrieve('brickrouge.form')
```

Note: Unlike the `widget` custom property, `brickrouge.form` does not create an instance, you
need to do that yourself. This might change is the future.





## Making private assets accessible from the web

Brickrouge can make unaccessible files–such as assets in the Phar–accessible from the web by
copying them to a directory defined by the `Brickrouge\ACCESSIBLE_ASSETS` constant :

```php
<?php

define('Brickrouge\ACCESSIBLE_ASSETS', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);

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

This consolidates the various CSS and JavaScript files and create compressed files in the
"assets/" directory. Files containing only the differences with Bootstrap are also
created ("-lite.css"). The following files are created:

* [brickrouge-lite.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge-lite.css)
* [brickrouge.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.css)
* [brickrouge.js](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.js)
* [responsive.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/responsive.css)

Note that you need the [LESS](http://lesscss.org/) compiler to compile the CSS files. JavaScript
files are compressed using the [online UglifyJS JavaScript minification](http://marijnhaverbeke.nl/uglifyjs/).





## More information

For more information and a demonstration please visit the [Brickrouge homepage](http://brickrouge.org/).





----------





## Requirements

The package requires PHP 5.4 or later.
The following packages are also required: [icanboogie/prototype](https://packagist.org/packages/icanboogie/prototype)
and [icanboogie/errors](https://packagist.org/packages/icanboogie/errors).





## Installation

The recommended way to install this package is through [composer](http://getcomposer.org/):

```
$ composer require brickrouge/brickrouge:
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Brickrouge/Brickrouge), its repository can
be cloned with the following command line:

	$ git clone https://github.com/Brickrouge/Brickrouge.git





## Documentation

You can generate the documentation for the package and its dependencies with the `make doc` command. The documentation is generated in the `build/docs` directory. [ApiGen](http://apigen.org/) is required. The directory can later be cleaned with the `make clean` command.





## Testing

The test suite is ran with the `make test` command. [PHPUnit](https://phpunit.de/) and [Composer](http://getcomposer.org/) need to be globally available to run the suite. The command installs dependencies as required. The `make test-coverage` command runs test suite and also creates an HTML coverage report in "build/coverage". The directory can later be cleaned with the `make clean` command.

The package is continuously tested by [Travis CI](http://about.travis-ci.org/).

[![Build Status](https://img.shields.io/travis/Brickrouge/Brickrouge.svg)](https://travis-ci.org/Brickrouge/Brickrouge)
[![Code Coverage](https://img.shields.io/coveralls/Brickrouge/Brickrouge.svg)](https://coveralls.io/r/Brickrouge/Brickrouge)




## License

**Brickrouge** is licensed under the New BSD License. See the [LICENSE](LICENSE) file for details.




[A]: http://brickrouge.org/docs/class-Brickrouge.A.html
[Actions]: http://brickrouge.org/docs/class-Brickrouge.Actions.html
[Alert]: http://brickrouge.org/docs/class-Brickrouge.Alert.html
[Button]: http://brickrouge.org/docs/class-Brickrouge.Button.html
[Element]: http://brickrouge.org/docs/class-Brickrouge.Element.html
[Form]: http://brickrouge.org/docs/class-Brickrouge.Form.html
[Group]: http://brickrouge.org/docs/class-Brickrouge.Group.html
[Modal]: http://brickrouge.org/docs/class-Brickrouge.Modal.html
[Popover]: http://brickrouge.org/docs/class-Brickrouge.Popover.html
[Searchbox]: http://brickrouge.org/docs/class-Brickrouge.Searchbox.html
[Text]: http://brickrouge.org/docs/class-Brickrouge.Text.html

[ICanBoogie]: http://icanboogie.org/
