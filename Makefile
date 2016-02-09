BRICKROUGE = ./assets/brickrouge
BRICKROUGE_LESS = ./lib/brickrouge.less
BRICKROUGE_UNCOMPRESSED = ./assets/brickrouge-uncompressed
BRICKROUGE_RESPONSIVE_LESS = ./lib/responsive.less
BRICKROUGE_RESPONSIVE = ./assets/responsive
BRICKROUGE_RESPONSIVE_UNCOMPRESSED = ./assets/responsive-uncompressed
BRICKROUGE_LITE = ./assets/brickrouge-lite
BRICKROUGE_LITE_UNCOMPRESSED = ./assets/brickrouge-lite-uncompressed
BRICKROUGE_LITE_TMP = '/tmp/brickrouge-lite/'
BRICKROUGE_LITE_LESS = ${BRICKROUGE_LITE_TMP}brickrouge.less

JS_COMPRESSOR = curl -X POST -s --data-urlencode 'js_code@$^' --data-urlencode 'utf8=1' http://marijnhaverbeke.nl/uglifyjs
#JS_COMPRESSOR = cat $^ # uncomment to create uncompressed files

CSS_COMPILER ?= `which sass`
CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
CSS_COMPRESSOR = cat $^ # uncomment to create uncompressed files

# CSS

CSS_FILES = \
	lib/actions.scss \
	lib/alert.scss \
	lib/brickrouge.scss \
	lib/form.scss \
	lib/popover.scss

CSS_COMPRESSED = assets/brickrouge.css
CSS_UNCOMPRESSED = assets/brickrouge-uncompressed.css

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

JS_COMPRESSED = assets/brickrouge.js
JS_UNCOMPRESSED = assets/brickrouge-uncompressed.js

all: \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(CSS_COMPRESSED) \
	$(CSS_UNCOMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	$(CSS_COMPILER) lib/brickrouge.scss >$@

watch:
	echo "Watching SCSS files..."
	$(CSS_COMPILER) --watch lib/brickrouge.scss:assets/brickrouge.css

# customization

PACKAGE_NAME = brickrouge/brickrouge
PACKAGE_VERSION = 2.3.0

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
