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

CSSDIR="css"
SKINDIR="css/skin"
CSSEXT=".css"
MINCSSEXT=".min.css"

rm -f ${JSDIR}/*${MINEXT}

for i in ${JSDIR}/*${JSEXT}; do
	echo "Minifying ${i}..."
	java -jar "${COMPRESSORDIR}/${COMPRESSORJAR}" --charset utf-8 -o "${i%$JSEXT}${MINEXT}" "${i}"
done

rm -f ${CSSDIR}/*${MINCSSEXT}

for i in ${CSSDIR}/*${CSSEXT}; do
	echo "Minifying ${i}..."
	java -jar "${COMPRESSORDIR}/${COMPRESSORJAR}" --charset utf-8 -o "${i%$CSSEXT}${MINCSSEXT}" "${i}"
done

rm -f ${SKINDIR}/*/*${MINCSSEXT}

for i in ${SKINDIR}/*; do
	for j in ${i}/*${CSSEXT}; do
		echo "Minifying ${j}..."
		java -jar "${COMPRESSORDIR}/${COMPRESSORJAR}" --charset utf-8 -o "${j%$CSSEXT}${MINCSSEXT}" "${j}"
	done
done
