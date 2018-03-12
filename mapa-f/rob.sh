#!/bin/bash

date > ./log.txt

./oc2mp.php
for plik in mapy/*.mp
do
	echo "wine compiling $plik"
        wine ./cgpsmapper.exe $plik -ieq -n geocaches >> ./log.txt  2>> ./log.txt
        echo "		... $plik done!"
done

cd ./mapy
wine ../cgpsmapper.exe pvx preview.txt >> ../log.txt  2>> ../log.txt

rm *.mp


cd ../
./patch_TDB.pl
cd ./mapy 

rm ../out/geocaches.zip
zip -5Jruq ../out/geocaches.zip * >> ./log.txt  2>> ./log.txt


#cp /www/rozne/geocache/oc.img /home/pliki/pub/mapy/gps/oc.img

wine ../nsis/makensis.exe install.nsi >> ./log.txt  2>> ./log.txt

cd ../out/
md5sum geocaches.zip > geocaches.zip.md5
du -h geocaches.zip >> geocaches.zip.md5
date -r geocaches.zip -R >> geocaches.zip.md5

md5sum geocaches.exe > geocaches.exe.md5
du -h geocaches.exe >> geocaches.exe.md5
date -r geocaches.exe -R >> geocaches.exe.md5

cd ..
date >> ./log.txt