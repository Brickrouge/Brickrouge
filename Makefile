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
LESS_COMPILER ?= `which lessc`
WATCHR ?= `which watchr`

# CSS

CSS_FILES = \
	lib/alerts.less \
	lib/forms.less \
	lib/responsive-767px-max.less \
	lib/brickrouge.less \
	lib/grid.less \
	lib/responsive-768px-979px.less \
	lib/button-groups.less \
	lib/layouts.less \
	lib/responsive.less \
	lib/buttons.less \
	lib/mixins.less \
	lib/responsive-navbar.less \
	lib/carousel.less \
	lib/navs.less \
	lib/responsive-utilities.less \
	lib/close.less \
	lib/popovers.less \
	lib/searchbox.less \
	lib/dropdowns.less \
	lib/reset.less \
	lib/utilities.less \
	lib/element.less \
	lib/responsive-1200px-min.less \
	lib/variables.less

CSS_COMPRESSOR = `which lessc`
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
	lib/alerts.js \
	lib/dropdowns.js \
	lib/navs.js \
	lib/popover.js \
	lib/tooltip.js \
	lib/searchbox.js \
	lib/carousel.js

JS_COMPRESSOR = curl -X POST -s --data-urlencode 'js_code@$^' --data-urlencode 'utf8=1' http://marijnhaverbeke.nl/uglifyjs
JS_COMPRESSOR = cat $^ # uncomment to create un compressed files
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

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) -x lib/brickrouge.less >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) lib/brickrouge.less >$@

$(CSS_RESPONSIVE_COMPRESSED): $(CSS_RESPONSIVE_FILES)
	$(CSS_COMPRESSOR) -x lib/responsive.less >$@

$(CSS_RESPONSIVE_UNCOMPRESSED): $(CSS_RESPONSIVE_FILES)
	$(CSS_COMPRESSOR) lib/responsive.less >$@

#build:
#	@@if test ! -z ${LESS_COMPILER}; then \
#		lessc ${BRICKROUGE_LESS} > ${BRICKROUGE_UNCOMPRESSED}.css; \
#		lessc -x ${BRICKROUGE_LESS} > ${BRICKROUGE}.css; \
#		lessc ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE_UNCOMPRESSED}.css; \
#		lessc -x ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE}.css; \
#		rm -fR ${BRICKROUGE_LITE_TMP}; \
#		mkdir ${BRICKROUGE_LITE_TMP}; \
#		php ./build/diff.php ${BRICKROUGE_LITE_TMP}; \
#		lessc ${BRICKROUGE_LITE_LESS} > ${BRICKROUGE_LITE_UNCOMPRESSED}.css; \
#		lessc -x ${BRICKROUGE_LITE_LESS} > ${BRICKROUGE_LITE}.css; \
#		echo "Brickrouge successfully built! - `date`"; \
#	else \
#		echo "You must have the LESS compiler installed in order to build Brickrouge."; \
#		echo "You can install it by running: npm install less -g"; \
#	fi
#

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

# customization

PACKAGE_NAME = "Brickrouge"

# do not edit the following lines

usage:
	@echo "test:  Runs the test suite.\ndoc:   Creates the documentation.\nclean: Removes the documentation, the dependencies and the Composer files."

composer.phar:
	@echo "Installing composer..."
	@curl -s https://getcomposer.org/installer | php

vendor: composer.phar
	@php composer.phar install --prefer-source --dev

update: vendor
	@php composer.phar update --prefer-source --dev

autoload: vendor
	@php composer.phar dump-autoload

test: vendor
	@phpunit

doc: vendor
	@mkdir -p "docs"

	@apigen \
	--source ./ \
	--destination docs/ --title $(PACKAGE_NAME) \
	--exclude "*/composer/*" \
	--exclude "*/tests/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	@rm -fR docs
	@rm -fR vendor
	@rm -f composer.lock
	@rm -f composer.phar

.PHONY: build watch