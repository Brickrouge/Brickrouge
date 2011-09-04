BRICKROUGE = ./assets/brickrouge.css
BRICKROUGE_UNCOMPRESSED = ./assets/brickrouge-uncompressed.css
BRICKROUGE_LESS = ./lib/brickrouge.less
LESS_COMPRESSOR ?= `which lessc`
WATCHR ?= `which watchr`

build:
	@@if test ! -z ${LESS_COMPRESSOR}; then \
		lessc ${BRICKROUGE_LESS} > ${BRICKROUGE_UNCOMPRESSED}; \
		lessc -x ${BRICKROUGE_LESS} > ${BRICKROUGE}; \
		lessc -x ./lib/scaffolding.less > ./assets/scaffolding.css; \
		echo "BrickRouge successfully built! - `date`"; \
	else \
		echo "You must have the LESS compiler installed in order to build BrickRouge."; \
		echo "You can install it by running: npm install less -g"; \
	fi

phar:
	@php -d phar.readonly=0 phar.make.php;

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"
	watchr -e "watch('lib/element/.*\.less') { system 'make' }"
	watchr -e "watch('lib/widget/.*\.less') { system 'make' }"

.PHONY: build watch
