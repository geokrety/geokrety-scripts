#!/bin/bash

/usr/local/bin/php ./ostatnie_logi.php
. ../konfig-tools.sh

markers=`cat ./lastlogs.txt`

url="http://maps.googleapis.com/maps/api/staticmap?size=500x500&markers=size:small|$markers&sensor=false&key=$GOOGLE_MAP_KEY"

curl -L -s -S -f "$url" -o ${GEOKRETY_WWW}/mapki/google_static_logs.png || echo $url
