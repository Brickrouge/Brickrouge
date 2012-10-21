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

build:
	@@if test ! -z ${LESS_COMPILER}; then \
		lessc ${BRICKROUGE_LESS} > ${BRICKROUGE_UNCOMPRESSED}.css; \
		lessc -x ${BRICKROUGE_LESS} > ${BRICKROUGE}.css; \
		lessc ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE_UNCOMPRESSED}.css; \
		lessc -x ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE}.css; \
		rm -fR ${BRICKROUGE_LITE_TMP}; \
		mkdir ${BRICKROUGE_LITE_TMP}; \
		php ./build/diff.php ${BRICKROUGE_LITE_TMP}; \
		lessc ${BRICKROUGE_LITE_LESS} > ${BRICKROUGE_LITE_UNCOMPRESSED}.css; \
		lessc -x ${BRICKROUGE_LITE_LESS} > ${BRICKROUGE_LITE}.css; \
		echo "Brickrouge successfully built! - `date`"; \
	else \
		echo "You must have the LESS compiler installed in order to build Brickrouge."; \
		echo "You can install it by running: npm install less -g"; \
	fi
	
	@cat ./lib/brickrouge.js ./lib/form.js ./lib/alerts.js ./lib/dropdowns.js ./lib/navs.js ./lib/popover.js ./lib/tooltip.js ./lib/searchbox.js ./lib/carousel.js > ${BRICKROUGE_UNCOMPRESSED}.js
	php ./build/compress.php ${BRICKROUGE_UNCOMPRESSED}.js ${BRICKROUGE}.js;

phar:
	@php -d phar.readonly=0 ./build/phar.php;

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

docs:
	apigen --source ./ --destination docs/ --title Brickrouge \
	--exclude "*/build/*" \
	--exclude "*/tests/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

.PHONY: build watch
