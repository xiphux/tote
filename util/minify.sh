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
	java -classpath lib/rhino/js.jar:lib/closure/compiler.jar org.mozilla.javascript.tools.shell.Main lib/requirejs/r.js -o name=${JSMODULE} out=${JSDIR}/${JSMODULE}${MINEXT} baseUrl=${JSDIR} paths.jquery="empty:" paths.qtip="empty:" paths.cookies="empty:" optimize="closure"
done

for i in ${CSSDIR}/*${CSSEXT}; do
	echo "Minifying ${i}..."
	CSSFILE=${i%$CSSEXT}
	java -classpath lib/rhino/js.jar org.mozilla.javascript.tools.shell.Main lib/requirejs/r.js -o cssIn=${i} out=${CSSFILE}${MINCSSEXT} optimizeCss="standard"
done

for i in ${SKINDIR}/*; do
	for j in ${i}/*${CSSEXT}; do
		echo "Minifying ${j}..."
		SKINCSSFILE=${j%$CSSEXT}
		java -classpath lib/rhino/js.jar org.mozilla.javascript.tools.shell.Main lib/requirejs/r.js -o cssIn=${j} out=${SKINCSSFILE}${MINCSSEXT} optimizeCss="standard"
	done
done

for i in ${JSDIR}/*${MINEXT}; do
	gzip -v -c ${i} > ${i}${GZEXT}
	touch ${i} ${i}${GZEXT}
done

for i in ${JSDIR}/ext/*${JSEXT}; do
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
