BRICKROUGE = ./assets/brickrouge
BRICKROUGE_JS = ./node_modules/brickrouge/dist/brickrouge.js
BRICKROUGE_JS_VERSION = master

JS_COMPILER = `which webpack`
JS_COMPILER_OPTIONS = -p                    # comment to disable compression
CSS_COMPILER = `which sass`
CSS_COMPILER_OPTIONS = --style compressed   # comment to disable compression

#

JS_COMPRESSED = $(BRICKROUGE).js
CSS_COMPRESSED = $(BRICKROUGE).css
JS_FILES = $(shell ls lib/*.js)
CSS_FILES = $(shell ls lib/*.scss)

all: \
	$(BRICKROUGE_JS) \
	$(JS_COMPRESSED) \
	$(CSS_COMPRESSED)

$(JS_COMPRESSED): $(JS_FILES)
	$(JS_COMPILER) $(JS_COMPILER_OPTIONS)

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) lib/Brickrouge.scss:$@

$(BRICKROUGE_JS): node_modules

node_modules:
	npm install

watch:
	echo "Watching files..."
	$(CSS_COMPILER) --watch lib/Brickrouge.scss:$(BRICKROUGE).css && \
	$(JS_COMPILER)  --watch --progress --colors

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
