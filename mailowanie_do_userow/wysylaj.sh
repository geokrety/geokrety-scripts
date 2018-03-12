#!/bin/bash


# gpg --clearsign --local-user geokrety.org ruchy-test.dat
plik=tresc.txt.asc


while read line
do
    adres=`echo $line | awk '{ print $1 }'`
    username=`echo $line | awk '{ print $2 }'`

    echo "$adres	$username"

    mutt -s "[Geokrety] small survey / ankieta" "$username <$adres>" < $plik

    rand=$((RANDOM%40+10))
    echo "Czekam $rand s ..."
    sleep $rand

done < adresy

date
