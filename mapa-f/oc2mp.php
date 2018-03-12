#!/usr/bin/env php
<?php

$KATALOG_MAP = "./mapy";
$opencaching = array("OC", "OP", "OZ", "ON", "OU", "OK", "OS", "OL", "OJ", "OB");
$ruskie = array("VI", "TR", "MS", "EX");


include_once("../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");
$link = mysql_pconnect($config['host'], $config['username'], $config['pass']);
mysql_select_db ($config['db']);

$HEAD = "[IMG ID]
ID=60845%03s
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

$jakie_pasy=4;	// co tyle stopni
for($PAS_lon=-180; $PAS_lon < 180; $PAS_lon=$PAS_lon+$jakie_pasy){

//$jakie_pasy=0.6;	// co tyle stopni
//for($PAS_lon=21.0; $PAS_lon < 21.6; $PAS_lon=$PAS_lon+$jakie_pasy){


$PAS_lon2=$PAS_lon+$jakie_pasy;
$count++;

$query = "SELECT waypoint, lat, lon, owner, typ, name, status
FROM `gk-waypointy`
WHERE `lon` between '$PAS_lon' AND '$PAS_lon2' AND `waypoint` NOT LIKE 'GD%' AND `waypoint` NOT LIKE 'OX%' AND `status` = '1' AND lat > -89 AND lat < 90 and lon > -180 and lon < 180";
//$status!='1'
//WHERE `lon` between '$PAS_lon' AND '$PAS_lon2'";


$result = mysql_query ($query) or die("ze zapytanie!");

while($row = mysql_fetch_array ($result))
{
list($waypoint, $lat, $lon, $owner, $typ, $name, $status) = $row;


$prefix=substr($waypoint,0,2);
$prefix3=substr($waypoint,0,3);

$typ = substr($typ, 0,2);
$owner = substr($owner, 0,6);

if($prefix=="GD"){
        $ikonka="0x6604";		// geodashing
}
elseif($prefix3=='WPG'){
	$ikonka="0x6603";		// waypoint game
}
elseif(in_array($prefix,$opencaching)){
	$ikonka="0x6602";		// któreś z oc
}
elseif($prefix=="GE"){
        $ikonka="0x6605";		// gpsgames
}
elseif($prefix=="GA"){
        $ikonka="0x6606";		// australia
}
elseif($prefix=="GR"){
        $ikonka="0x6607";		// romania
}
elseif($prefix=="RH"){
        $ikonka="0x6608";		// transsylwania
}
elseif(in_array($prefix,$ruskie)){
	$ikonka="0x6609";		// ruskie
}
else{
        $ikonka="0x6600";		// inne
}

// jeśli status nieaktywny...
if($status!='1'){
    $status = "?";
    $ikonka="0x6601";
}
else $status="";

if($lat == -90) $lat = -89;

$dane.="[POI]
Type=$ikonka
Label=$waypoint $status ($typ/$owner)
Data0=($lat,$lon)
EndLevel=1
[END]
";
}

mysql_free_result($result);


if($dane!=""){
    $plik = sprintf("60845%03s", $count);
    $OUT = sprintf($HEAD, $count) . "\n\n" . $dane;
    file_put_contents("$KATALOG_MAP/$plik.mp", $OUT . "\n\n\n");
    $SPIS_MAP .= "img=$plik.img\n";
    }

unset($dane);
}


// -------------------- priwiu --------------------------//

$data=date("yMd");

$PV="[Map]
FileName=geocaches
MapVersion=100
ProductCode=1
FID=39452
Levels=2
Level0=13
Level1=12
Zoom0=2
Zoom1=3

MapsourceName=geocache/GK $data
MapSetName=geocache/GK $data
CDSetName=geocache/GK $data
[End-Map]

[Files]
$SPIS_MAP
[END-Files]";

file_put_contents($KATALOG_MAP . "/preview.txt", $PV);

?>
