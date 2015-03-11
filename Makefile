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

CSS_COMPILER ?= `which lessc`
CSS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://cssminifier.com/raw
#CSS_COMPRESSOR = cat $^ # uncomment to create uncompressed files

WATCHR ?= `which watchr`

# CSS

CSS_FILES = \
	lib/alert.less \
	lib/brickrouge.less \
	lib/button.less \
	lib/button-groups.less \
	lib/carousel.less \
	lib/close.less \
	lib/dropdowns.less \
	lib/element.less \
	lib/form.less \
	lib/grid.less \
	lib/layouts.less \
	lib/mixins.less \
	lib/modal.less \
	lib/navs.less \
	lib/popover.less \
	lib/reset.less \
	lib/responsive.less \
	lib/responsive-1200px-min.less \
	lib/responsive-767px-max.less \
	lib/responsive-768px-979px.less \
	lib/responsive-navbar.less \
	lib/responsive-utilities.less \
	lib/searchbox.less \
	lib/utilities.less \
	lib/variables.less

CSS_COMPRESSED = assets/brickrouge.css
CSS_UNCOMPRESSED = assets/brickrouge-uncompressed.css

CSS_RESPONSIVE_FILES = \
	lib/responsive-1200px-min.less \
	lib/responsive-767px-max.less \
	lib/responsive-768px-979px.less

CSS_RESPONSIVE_COMPRESSED = assets/brickrouge-responsive.css
CSS_RESPONSIVE_UNCOMPRESSED = assets/brickrouge-responsive-uncompressed.css

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
	$(CSS_UNCOMPRESSED) \
	$(CSS_RESPONSIVE_COMPRESSED) \
	$(CSS_RESPONSIVE_UNCOMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	$(CSS_COMPILER) lib/brickrouge.less >$@

$(CSS_RESPONSIVE_COMPRESSED): $(CSS_RESPONSIVE_UNCOMPRESSED)
	$(CSS_COMPRESSOR) >$@

$(CSS_RESPONSIVE_UNCOMPRESSED): $(CSS_RESPONSIVE_FILES)
	$(CSS_COMPILER) lib/responsive.less >$@

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

# customization

PACKAGE_NAME = "Brickrouge"
PACKAGE_VERSION = 2.1.4

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
