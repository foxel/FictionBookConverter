#!/bin/bash

PHPSCRIPT=`echo "$0" | sed 's/\\.sh$/.php/'`;

find . -name '*.fb2' -exec php -f "${PHPSCRIPT}" "{}" \;

