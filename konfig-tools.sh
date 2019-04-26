#!/bin/sh

# simple wrapper that can permit to define variables using envvars
# without updating every script requiring this file

GEOKRETY_WWW=/var/www/html/

# export bypass
KOCHAM_KACZYNSKIEGO=${EXPORT_BYPASS_TOKEN:-xxx}

GOOGLE_MAP_KEY=${GOOGLE_MAP_KEY:-xxx}

FILE=konfig-tools.local.sh
test -f $FILE && source $FILE
