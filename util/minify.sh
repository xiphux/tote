#!/bin/bash
#
# minify.sh
#
# Minfies javascript files
#
# @author Christopher Han <xiphux@gmail.com>
# @copyright Copyright (c) 2010 Christopher Han
# @package GitPHP
# @subpackage util
#

JSDIR="js"
COMPRESSORDIR="lib/yuicompressor/build"
COMPRESSORJAR="yuicompressor-2.4.2.jar"

JSEXT=".js"
MINEXT=".min.js"
GZEXT=".gz"

CSSDIR="css"
SKINDIR="css/skin"
CSSEXT=".css"
MINCSSEXT=".min.css"

rm -fv ${JSDIR}/*${MINEXT}
rm -fv ${JSDIR}/*${GZEXT}
rm -fv ${JSDIR}/ext/*${GZEXT}
rm -fv ${CSSDIR}/*${MINCSSEXT}
rm -fv ${CSSDIR}/*${GZEXT}
rm -fv ${CSSDIR}/ext/*${GZEXT}
rm -fv ${SKINDIR}/*/*${MINCSSEXT}
rm -fv ${SKINDIR}/*/*${GZEXT}

if [ "$1" == "clean" ]; then
	exit;
fi

for i in ${JSDIR}/*${JSEXT}; do
	echo "Minifying ${i}..."
	JSMODULE="`basename ${i%$JSEXT}`"
	node lib/requirejs/r.js -o util/build.js name=${JSMODULE} out=${JSDIR}/${JSMODULE}${MINEXT}.tmp optimize="none"
	uglifyjs ${JSDIR}/${JSMODULE}${MINEXT}.tmp -c -m -o ${JSDIR}/${JSMODULE}${MINEXT}.tmp2
	cat util/jsheader.js ${JSDIR}/${JSMODULE}${MINEXT}.tmp2 > ${JSDIR}/${JSMODULE}${MINEXT}
	rm -f ${JSDIR}/${JSMODULE}${MINEXT}.tmp ${JSDIR}/${JSMODULE}${MINEXT}.tmp2
done

for i in ${CSSDIR}/*${CSSEXT}; do
	echo "Minifying ${i}..."
	CSSFILE=${i%$CSSEXT}
	node lib/requirejs/r.js -o cssIn=${i} out=${CSSFILE}${MINCSSEXT} optimizeCss="standard"
done

for i in ${SKINDIR}/*; do
	for j in ${i}/*${CSSEXT}; do
		echo "Minifying ${j}..."
		SKINCSSFILE=${j%$CSSEXT}
		node lib/requirejs/r.js -o cssIn=${j} out=${SKINCSSFILE}${MINCSSEXT} optimizeCss="standard"
	done
done

for i in ${JSDIR}/*${MINEXT}; do
	gzip -v -c ${i} > ${i}${GZEXT}
	touch ${i} ${i}${GZEXT}
done

for i in ${JSDIR}/ext/*${MINEXT}; do
	gzip -v -c ${i} > ${i}${GZEXT}
	touch ${i} ${i}${GZEXT}
done

for i in ${CSSDIR}/*${MINCSSEXT}; do
	gzip -v -c ${i} > ${i}${GZEXT}
	touch ${i} ${i}${GZEXT}
done

for i in ${CSSDIR}/ext/*${MINCSSEXT}; do
	gzip -v -c ${i} > ${i}${GZEXT}
	touch ${i} ${i}${GZEXT}
done

for i in ${SKINDIR}/*; do
	for j in ${i}/*${MINCSSEXT}; do
		gzip -v -c ${j} > ${j}${GZEXT}
		touch ${j} ${j}${GZEXT}
	done
done
