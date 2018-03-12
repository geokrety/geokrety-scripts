#!/usr/bin/env php
<?php

$KATALOG_MAP = "./mapy";


$HEAD = "[IMG ID]
ID=60846%03s
Name=geokrety.org
Elevation=M
Preprocess=F
CodePage=1250
LblCoding=9
TreSize=1138
TreMargin=0.00000
RgnLimit=1024
Transparent=Y
POIIndex=Y
POIOnly=Y
Levels=3
Level0=24
Level1=22
Level2=16
Zoom0=0
Zoom1=1
Zoom2=2
[END-IMG ID]

";

$jakie_pasy=10;	// co tyle stopni
for($PAS_lon=-180; $PAS_lon < 180; $PAS_lon=$PAS_lon+$jakie_pasy){


$PAS_lon2=$PAS_lon+$jakie_pasy;
$count++;


// ---------------------------------------- przeciêcia -------------------------------------- //



for($latP = -85; $latP <= 85; $latP++){
    for($lonP = $PAS_lon; $lonP <= $PAS_lon2; $lonP++){
$dane.="[POI]
Type=0x6603
Label=Conf $latP/$lonP
Data0=($latP,$lonP)
EndLevel=1
[END]
";
    } 
}



if($dane!=""){
    $plik = sprintf("60846%03s", $count);
    $OUT = sprintf($HEAD, $count) . "\n\n" . $dane;
    file_put_contents("$KATALOG_MAP/$plik.mp", $OUT . "\n\n\n");
    $SPIS_MAP .= "img=$plik.img\n";
    }

unset($dane);
}

// -------------------- priwiu --------------------------//


$PV="[Map]
FileName=geocaches
MapVersion=100
ProductCode=1
FID=39454
Levels=2
Level0=13
Level1=12
Zoom0=2
Zoom1=3

MapsourceName=degree confluence / geokrety.org
MapSetName=degree confluence / geokrety.org
CDSetName=degree confluence / geokrety.org
[End-Map]

[Files]
$SPIS_MAP
[END-Files]";

file_put_contents($KATALOG_MAP . "/preview.txt", $PV);

?>
