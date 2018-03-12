#!/bin/bash

# gpg -b --armour --local-user 76B00039 plik.txt
#gpg --clearsign --armour --local-user 76B00039 plik.txt


#mutt -s "Bekap SQLa" -a  $KATALOG_GPG/all.sql.bz2.gpg stefaniak.backup@gmail.com < /proc/uptime

mutt -s "[GeoKrety] test"  "stefaniak@gmail.com" < tresc.txt

