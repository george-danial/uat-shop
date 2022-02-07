#!/bin/bash

SCRIPT=`realpath -s $0`
SCRIPTPATH=`dirname $SCRIPT`
parentdir="$(dirname "$SCRIPTPATH")"
parentdir="$(dirname "$parentdir")"
SCRIPTPATH="$parentdir/pub/media/"
echo $SCRIPTPATH
echo $parentdir
#find pub/media/ -name '*.png' -exec pngcrush -ow -rem allb -reduce {} \;
FILES=$(find "$parentdir" -type f -iname '*.png')

FIXED=0
for f in $FILES; do
    WARN=$(pngcrush -ow -rem allb -reduce "$f" 2>&1)
    if [[ "$WARN" == *"PCS illuminant is not D50"* ]] || [[ "$WARN" == *"known incorrect sRGB profile"* ]]; then
        pngcrush -s -ow -rem allb -reduce "$f"
        FIXED=$((FIXED + 1))
    fi
done

echo "$FIXED errors fixed"

n98-magerun --root-dir $parentdir catalog:images:resize
