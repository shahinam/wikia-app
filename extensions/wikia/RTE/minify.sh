#!/bin/bash
# This shell script is used to concatenate and minify JS files (CK core and RTE code)
svn up

cd ckeditor && java -jar ../_minify/ckpackager.jar ckeditor.wikia.pack # in case of errors read http://dev.fckeditor.net/ticket/4693

# perform %VERSION% and %REVISION% replacements
version=`date +%Y%m%d`
rev=`svn info | tail -n 7 | head -n 1 | awk '{ print $2 }'`

sed -i "s/%VERSION%/$version/g" ckeditor.js
sed -i "s/%REV%/r$rev/g" ckeditor.js

# minify wikia skin CSS files
cd ../_minify/ && ./minifySkinCss.php
