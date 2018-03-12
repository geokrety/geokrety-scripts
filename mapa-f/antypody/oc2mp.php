#!/usr/bin/env php
<?php

$KATALOG_MAP = "./mapy";

include_once("../../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");
$link = mysql_pconnect($config['host'], $config['username'], $config['pass']);
mysql_select_db ($config['db']);

$HEAD = "[IMG ID]
ID=60847%03s
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
Level0=20
Level1=17
Level2=10
Zoom0=0
Zoom1=1
Zoom2=2
[END-IMG ID]

";

$jakie_pasy=10;	// co tyle stopni
for($PAS_lon=-180; $PAS_lon < 180; $PAS_lon=$PAS_lon+$jakie_pasy){

//$jakie_pasy=0.6;	// co tyle stopni
//for($PAS_lon=21.0; $PAS_lon < 21.6; $PAS_lon=$PAS_lon+$jakie_pasy){


$PAS_lon2=$PAS_lon+$jakie_pasy;
$count++;

$query = "SELECT asciiname, lat, lon, country
FROM `gk-miasta`
WHERE `lon` between '$PAS_lon' AND '$PAS_lon2'";

$result = mysql_query ($query) or die("ze zapytanie!");

while($row = mysql_fetch_array ($result))
{
list($asciiname, $lat, $lon, $country) = $row;


$lat=-1*$lat;
$lon=$lon-(abs($lon)/$lon)*180;

$dane.="[POI]
Type=0x6604
Label=$asciiname ($country)
Data0=($lat,$lon)
EndLevel=1
[END]
";

unset($asciiname, $lat, $lon, $country);
}

mysql_free_result($result);

if($dane!=""){
    $plik = sprintf("60847%03s", $count);
        $OUT = sprintf($HEAD, $count) . "\n\n" . $dane;
        file_put_contents("$KATALOG_MAP/$plik.mp", $OUT . "\n\n\n");
        $SPIS_MAP .= "img=$plik.img\n";
    }
    unset($dane);
}

?>
