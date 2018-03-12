#!/bin/bash

/usr/local/bin/php ./ostatnie_logi.php

markers=`cat ./lastlogs.txt`

url="http://maps.googleapis.com/maps/api/staticmap?size=500x500&markers=size:small|$markers&sensor=false"

echo $url;

. ../konfig-tools.sh
wget "$url" -O ${GEOKRETY_WWW}mapki/google_static_logs.png
