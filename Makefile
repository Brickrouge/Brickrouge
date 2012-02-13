BRICKROUGE = ./assets/brickrouge
BRICKROUGE_UNCOMPRESSED = ./assets/brickrouge-uncompressed
BRICKROUGE_LESS = ./lib/brickrouge.less
BRICKROUGE_RESPONSIVE = ./assets/responsive
BRICKROUGE_RESPONSIVE_UNCOMPRESSED = ./assets/responsive-uncompressed
BRICKROUGE_RESPONSIVE_LESS = ./lib/responsive.less
LESS_COMPILER ?= `which lessc`
WATCHR ?= `which watchr`

build:
	@@if test ! -z ${LESS_COMPILER}; then \
		lessc ${BRICKROUGE_LESS} > ${BRICKROUGE_UNCOMPRESSED}.css; \
		lessc -x ${BRICKROUGE_LESS} > ${BRICKROUGE}.css; \
		lessc ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE_UNCOMPRESSED}.css; \
		lessc -x ${BRICKROUGE_RESPONSIVE_LESS} > ${BRICKROUGE_RESPONSIVE}.css; \
		echo "Brickrouge successfully built! - `date`"; \
	else \
		echo "You must have the LESS compiler installed in order to build Brickrouge."; \
		echo "You can install it by running: npm install less -g"; \
	fi
	
	@cat ./lib/brickrouge.js ./lib/form.js ./lib/alerts.js ./lib/dropdowns.js ./lib/popover.js ./lib/searchbox.js > ${BRICKROUGE_UNCOMPRESSED}.js
	php ./build/compress.php ${BRICKROUGE_UNCOMPRESSED}.js ${BRICKROUGE}.js;

phar:
	@php -d phar.readonly=0 ./build/phar.php;

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

.PHONY: build watch
