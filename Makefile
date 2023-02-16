PHPUNIT=vendor/bin/phpunit

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
JS_FILES = $(shell ls src/*.js)
CSS_FILES = $(shell ls src/*.scss)

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
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) src/Brickrouge.scss:$@

node_modules:
	yarn install

watch:
	echo "Watching files..."
	$(CSS_COMPILER) --watch src/Brickrouge.scss:$(BRICKROUGE).css && \
	$(JS_COMPILER) $(JS_COMPILER_OPTIONS) --watch --progress --colors

# customization

PACKAGE_NAME = brickrouge/brickrouge
PACKAGE_VERSION = 3.0.0

# do not edit the following lines

.PHONY: usage
usage:
	@echo "test:  Runs the test suite.\ndoc:   Creates the documentation.\nclean: Removes the documentation, the dependencies and the Composer files."

vendor:
	@composer install

update:
	@composer update
	@yarn upgrade

# testing

.PHONY: test-dependencies
test-dependencies: vendor

.PHONY: test
test: test-dependencies
	@$(PHPUNIT) $(ARGS)

.PHONY: test-coverage
test-coverage: test-dependencies
	@mkdir -p build/coverage
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-html build/coverage $(ARGS)

.PHONY: test-coveralls
test-coveralls: test-dependencies
	@mkdir -p build/logs
	@XDEBUG_MODE=coverage $(PHPUNIT) --coverage-clover build/logs/clover.xml

.PHONY: test-container
test-container: test-container-81

.PHONY: test-container-81
test-container-81:
	@-docker-compose run --rm app81 bash
	@docker-compose down -v

.PHONY: test-container-82
test-container-82:
	@-docker-compose run --rm app82 bash
	@docker-compose down -v

.PHONY: lint
lint:
	@XDEBUG_MODE=off phpcs -s
	@XDEBUG_MODE=off vendor/bin/phpstan

doc: vendor
	@mkdir -p build/docs
	@apigen generate \
	--source src \
	--destination build/docs/ \
	--title "$(PACKAGE_NAME) $(PACKAGE_VERSION)" \
	--template-theme "bootstrap"

clean:
	@rm -fR .sass-cache
	@rm -fR build
	@rm -fR vendor
	@rm -f composer.lock
	@rm -fR node_modules

.PHONE: all autoload clean update usage watch
