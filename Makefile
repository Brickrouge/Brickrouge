BRICKROUGE = ./assets/brickrouge
BRICKROUGE_UNCOMPRESSED = ./build/tmp/brickrouge-uncompressed

JS_COMPILER = cat
JS_COMPRESSOR = curl -X POST -s --data-urlencode 'js_code@$^' --data-urlencode 'utf8=1' http://marijnhaverbeke.nl/uglifyjs
#JS_COMPRESSOR = cat $^ # uncomment to create uncompressed files
CSS_COMPILER = `which sass`
CSS_COMPILER_OPTIONS = --style compressed   # comment to disable compression

#

JS_COMPRESSED = $(BRICKROUGE).js
CSS_COMPRESSED = $(BRICKROUGE).css
JS_FILES = $(shell ls lib/*.js)
CSS_FILES = $(shell ls lib/*.scss)

all: \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(CSS_COMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	@mkdir -p ./build/tmp
	$(JS_COMPILER) $^ >$@

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) lib/brickrouge.scss:$@

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
