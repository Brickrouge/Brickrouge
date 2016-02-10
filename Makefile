BRICKROUGE = ./assets/brickrouge
BRICKROUGE_UNCOMPRESSED = ./build/tmp/brickrouge-uncompressed

JS_COMPILER = cat
JS_COMPRESSOR = curl -X POST -s --data-urlencode 'js_code@$^' --data-urlencode 'utf8=1' http://marijnhaverbeke.nl/uglifyjs
#JS_COMPRESSOR = cat $^ # uncomment to create uncompressed files

CSS_COMPILER ?= `which sass`
CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
#CSS_COMPRESSOR = cat $^ # uncomment to create uncompressed files

# CSS

CSS_FILES = \
	lib/actions.scss \
	lib/alert.scss \
	lib/brickrouge.scss \
	lib/form.scss \
	lib/popover.scss

CSS_COMPRESSED = $(BRICKROUGE).css
CSS_UNCOMPRESSED = $(BRICKROUGE_UNCOMPRESSED).css

# JavaScript

JS_FILES = \
	lib/brickrouge.js \
	lib/form.js \
	lib/alert.js \
	lib/dropdowns.js \
	lib/navs.js \
	lib/popover.js \
	lib/modal.js \
	lib/tooltip.js \
	lib/searchbox.js \
	lib/carousel.js

JS_COMPRESSED = $(BRICKROUGE).js
JS_UNCOMPRESSED = $(BRICKROUGE_UNCOMPRESSED).js

all: \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(CSS_COMPRESSED) \
	$(CSS_UNCOMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	@mkdir -p ./build/tmp
	$(JS_COMPILER) $^ >$@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	@mkdir -p ./build/tmp
	$(CSS_COMPILER) lib/brickrouge.scss >$@

watch:
	echo "Watching SCSS files..."
	$(CSS_COMPILER) --watch lib/brickrouge.scss:$(BRICKROUGE).css

# customization

PACKAGE_NAME = brickrouge/brickrouge
PACKAGE_VERSION = 3.0.0

# do not edit the following lines

usage:
	@echo "test:  Runs the test suite.\ndoc:   Creates the documentation.\nclean: Removes the documentation, the dependencies and the Composer files."

vendor:
	@composer install

update:
	@composer update

autoload: vendor
	@composer dump-autoload

test: vendor
	@phpunit

test-coverage: vendor
	@mkdir -p build/coverage
	@phpunit --coverage-html build/coverage

doc: vendor
	@mkdir -p build/docs
	@apigen generate \
	--source lib \
	--destination build/docs/ \
	--title "$(PACKAGE_NAME) $(PACKAGE_VERSION)" \
	--template-theme "bootstrap"

clean:
	@rm -fR build
	@rm -fR vendor
	@rm -f composer.lock

.PHONY: build watch
