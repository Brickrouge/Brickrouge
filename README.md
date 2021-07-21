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
* Support localization
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

Brickrouge's widgets are what is generally called [custom HTML
elements](https://www.google.com/search?q=html+custome+elements). Widget types are associated with a
JavaScript constructor and Brickrouge makes sure that widgets are constructed when the DOM is ready
or updated, or when the `widget` custom property of an element is obtained.

> All widgets mechanisms are handled by the [Brickrouge.js][] library, you might want to check it
out.

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
!function (Brickrouge) {

	class Color {

		constructor(el, options) {

			el.setStyle('background', options.color)
			el.innerHTML = options.colorName

		}

	})

	Brickrouge.register('Color', (element, options) => {

		return new Color(element, options)

	})

} (Brickrouge);
```





### Obtaining the widget associated with an element

The `widget` custom property is used to obtain the widget associated with an element (if any). If
the widget has not yet been created, getting the property creates it. The element associated with a
widget is always available through its `element` property.

```js
const element = document.getElementById('my-color')
const color = Brickrouge.Widget.from(element)
// or
const color = Brickrouge.from(element)
```





### When a widget has been built

The `widget` event is fired after a widget has been built.

```js
Brickrouge.observeWidget(ev => {

	console.log('A widget has been built:', ev.widget)

})
```





### When the DOM is updated

The `update` event is fired after the DOM was updated.

```js
Brickrouge.observeUpdate(ev => {

    console.log('This fragment updated the DOM:', ev.fragment)
    console.log('These are new custom elements:', ev.elements)
    console.log('These widgets have been built:', ev.widgets)

})
```

> **Note:** The event is fired a first time after **Brickrouge** is ran.





## Forms

Forms are usually instances of the [Form][] class. The children of the form are specified using
the `CHILDREN` custom attribute, while the actions of the form are specified using `ACTIONS`.
For convenience, hidden values can be specified using `HIDDEN`.

```php
<?php

namespace Brickrouge;

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

	Form::CHILDREN => [

		'sender_name' => new Text([

			Group::LABEL => "Sender's name",

			Element::REQUIRED => true

		]),

		'sender_email' => new Text([

			Group::LABEL => "Sender's e-mail",

			Element::REQUIRED => true,
			Element::VALIDATION => 'email'

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
		<div class="form-group form-group--sender-name required">
			<label for="autoid--sender-name" class="form-control-label">Sender's name</label>
			<div class="controls">
				<input required="required" type="text" name="sender_name" id="autoid--sender-name" />
			</div>
		</div>

		<div class="form-group form-group--sender-email required">
			<label for="autoid--sender-email" class="form-control-label">Sender's e-mail</label>
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
const element = document.getElementById('myForm')
const form = new Brickrouge.Form(element)

form.observeComplete(ev => {

	console.log('complete:', ev.response)

});

form.submit()
```

[ICanBoogie][] operation responses are supported and the following properties are recognized:

- `errors`: An array of key/value where _key_ is the name of an element, and _value_ the error
message for that element.
- `message`: If the request is successful, this property is used as a _success_ message.

The class automatically creates alerts according to this properties. If the `replaceOnSuccess`
option is true the _success_ message is inserted before the form element, which is then hidden.





### Retrieving the instance associated with a form element

The `Form` instance associated with a form element can be retrieved with the `retrieve()` method:

```js
const element = document.getElementById('myForm')
const form = Brickrouge.Form.from(element)
```

> **Note:** Unlike `Brickrouge.from()`, `Brickrouge.Form.from()` does not create an instance, you
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

> **Note:** The directory must be writable by PHP.





## Patching Brickrouge

Brickrouge was initially designed to work with the framework [ICanBoogie][]. The project has evolved
to stand alone and now provides means to patch critical features such as translation, errors
handling or form storing/retrieving. Fallback for each feature are provided so you can patch what
you need and leave the rest.





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





## Building Brickrouge

Brickrouge comes with pre-built compressed CSS and JavaScript files, but you might want to play with
its source, in which case you might probably want to build it yourself. A Makefile is available for
this purpose.

Open a terminal, go to Brickrouge directory, and type "make":

```bash
$ cd /path/to/Brickrouge/
$ make
```

This consolidates the various CSS and JavaScript files and create compressed files in the "assets/"
directory. The following files are created:

* [brickrouge.css](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.css)
* [brickrouge.js](https://github.com/Brickrouge/Brickrouge/blob/master/assets/brickrouge.js)

Note that you need the [SASS](http://sass-lang.com/) compiler to compile the CSS files. JavaScript
files are compressed using the online [Closure](https://developers.google.com/closure/) compiler.





## More information

For more information and a demonstration please visit the [Brickrouge homepage](http://brickrouge.org/).





----------





## Requirements

The package requires PHP 5.5 or later.
The following packages are also required: [icanboogie/prototype](https://packagist.org/packages/icanboogie/prototype)
and [icanboogie/errors](https://packagist.org/packages/icanboogie/errors).





## Installation

The recommended way to install this package is through [composer](http://getcomposer.org/):

```
$ composer require brickrouge/brickrouge
```





### Cloning the repository

The package is [available on GitHub](https://github.com/Brickrouge/Brickrouge), its repository can
be cloned with the following command line:

	$ git clone https://github.com/Brickrouge/Brickrouge.git





## Documentation

You can generate the documentation for the package and its dependencies with the `make doc` command.
The documentation is generated in the `build/docs` directory. [ApiGen](http://apigen.org/) is
required. The directory can later be cleaned with the `make clean` command.





## Testing

We provide a Docker container for local development. Run `make test-container` to create a new session. Inside the
container run `make test` to run the test suite. Alternatively, run `make test-coverage` for a breakdown of the code
coverage. The coverage report is available in `build/coverage/index.html`.

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

[ICanBoogie]: https://github.com/ICanBoogie/ICanBoogie
[Brickrouge.js]: https://github.com/Brickrouge/Brickrouge.js
