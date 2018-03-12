#!/bin/bash

. konfig-tools.sh

now=`date +"%s"`

doba=86400; # w sekundach

# ile to sekund
diff_1m=`echo "$doba*31" | bc`
diff_1y=`echo "$doba*365" | bc`

# timestamp z wtedy
timestamp_1m=`echo $now-$diff_1m | bc`
timestamp_1y=`echo $now-$diff_1y | bc`

# i znow timestamp na ludzki język
modifiedsince_1m=`date +"%Y%m%d%H%M%S" -d @$timestamp_1m`
modifiedsince_1y=`date +"%Y%m%d%H%M%S" -d @$timestamp_1y`

#dodatkowo modifiedsince dla całej bazy
modifiedsince_all="20000510032102" # przypadkowa data z przeszłości :)

pobierz_i_spakuj(){
  #    wget "https://geokrety.org/export.php?modifiedsince=$1&kocham_kaczynskiego=$KOCHAM_KACZYNSKIEGO" -O "$GEOKRETY_WWW/rzeczy/xml/export-$2.xml"
  wget "https://geokrety.org/export2.php?modifiedsince=$1&kocham_kaczynskiego=$KOCHAM_KACZYNSKIEGO" -O "$GEOKRETY_WWW/rzeczy/xml/export2-$2.xml"
  wget "https://geokrety.org/export_oc.php?modifiedsince=$1&kocham_kaczynskiego=$KOCHAM_KACZYNSKIEGO" -O "$GEOKRETY_WWW/rzeczy/xml/export_oc-$2.xml"
  #    bzip2 "/home/geokrety/public_html/rzeczy/xml/export-$2.xml"
  bzip2 -f "$GEOKRETY_WWW/rzeczy/xml/export2-$2.xml"
  bzip2 -f "$GEOKRETY_WWW/rzeczy/xml/export_oc-$2.xml"
}

pobierz_i_spakuj $modifiedsince_1m "1month"
pobierz_i_spakuj $modifiedsince_1y "1year"
pobierz_i_spakuj $modifiedsince_all "full"
