#!/bin/bash

wget -O oc.com.gpx "http://www.opencaching.com/api/geocache.gpx?description=false&hint=false&log_limit=1&log_comment=false&limit=5000"
cat oc.com.gpx | sed 's/groundspeak:/groundspeak/g' > oc2.com.gpx
./gpx2sql.php
date > log.txt

rm oc2.com.gpx
rm oc.com.gpx