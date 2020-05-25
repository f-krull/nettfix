

websrcs := \
	src/web/js/tabulator.js \
	src/web/js/bootstrap.bundle.min.js.map \
	src/web/js/bootstrap.bundle.min.js \
	src/web/js/tabulator.min.js \
	src/web/js/jquery.min.js \
	src/web/css/bootstrap.min.css.map \
	src/web/css/tabulator.min.css.map \
	src/web/css/bootstrap.min.css \
	src/web/css/tabulator.min.css

src/web/%:
	zcat 3rdparty/$*.gz > $@ 

all: $(websrcs)
