BRICKROUGE = ./assets/brickrouge
BRICKROUGE_UNCOMPRESSED = ./build/brickrouge-uncompressed.js
BRICKROUGE_JS = ./node_modules/brickrouge/dist/brickrouge-uncompressed.js

JS_COMPILER = `which webpack`
JS_COMPILER_OPTIONS = --display-modules
CSS_COMPILER = `which sass`
CSS_COMPILER_OPTIONS = --style compressed   # comment to disable compression

JS_COMPRESSOR = `which uglifyjs` $^ \
	--compress \
	--mangle \
	--screw-ie8 \
	--source-map $@.map \
	--source-map-url https://github.com/Brickrouge/Brickrouge/tree/master/dist/$@.map
#JS_COMPRESSOR = cat $^ # uncomment to create uncompressed JS

JS_COMPRESSED = $(BRICKROUGE).js
CSS_COMPRESSED = $(BRICKROUGE).css
JS_FILES = $(shell ls lib/*.js)
CSS_FILES = $(shell ls lib/*.scss)

all: \
	$(BRICKROUGE_JS) \
	$(BRICKROUGE_UNCOMPRESSED) \
	$(JS_COMPRESSED) \
	$(CSS_COMPRESSED)

$(BRICKROUGE_JS): node_modules

$(BRICKROUGE_UNCOMPRESSED): $(JS_FILES)
	$(JS_COMPILER) $(JS_COMPILER_OPTIONS)

$(JS_COMPRESSED): $(BRICKROUGE_UNCOMPRESSED)
	$(JS_COMPRESSOR) > $@

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) lib/Brickrouge.scss:$@

node_modules:
	yarn install

watch:
	echo "Watching files..."
	$(CSS_COMPILER) --watch lib/Brickrouge.scss:$(BRICKROUGE).css && \
	$(JS_COMPILER) $(JS_COMPILER_OPTIONS) --watch --progress --colors

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
	@yarn upgrade

autoload: vendor
	@composer dump-autoload

test: vendor
	@phpunit

test-coverage: vendor
	@mkdir -p build/coverage
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html build/coverage $(ARGS)

.PHONY: test-container
test-container:
	@-docker-compose run --rm app bash
	@docker-compose down -v

doc: vendor
	@mkdir -p build/docs
	@apigen generate \
	--source lib \
	--destination build/docs/ \
	--title "$(PACKAGE_NAME) $(PACKAGE_VERSION)" \
	--template-theme "bootstrap"

clean:
	@rm -fR .sass-cache
	@rm -fR build
	@rm -fR vendor
	@rm -f composer.lock
	@rm -fR node_modules

.PHONE: all autoload clean update test test-coverage usage watch
