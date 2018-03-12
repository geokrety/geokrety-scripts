#!/usr/bin/env php
<?php

$KATALOG_MAP = "./mapy";

include("../../templates/konfig.php");        // config
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
Levels=2
Level0=22
Level1=16
Zoom0=0
Zoom1=1
[END-IMG ID]

";

$przedzialy = array ("0" => "lon < '-14'", "1" => "lon >= '-14' AND lon <= '38'", "2" => "lon > '38'");


foreach($przedzialy as $key => $przedzial){

$query = "SELECT waypoint, lat, lon, owner, typ, name, status
FROM `gk-waypointy`
WHERE $przedzial";

$result = mysql_query ($query) or die("ze zapytanie!");

while($row = mysql_fetch_array ($result))
{
list($waypoint, $lat, $lon, $owner, $typ, $name, $status) = $row;

$typ = substr($typ, 0,2);
$owner = substr($owner, 0,3);
if($status!='1') $status = "?";
    else $status = "";
if($lat == -90) $lat = -89;

$dane.="[POI]
Type=0x6619
Label=$waypoint ($typ/$owner)$status
Data0=($lat,$lon)
[END]
";




}


mysql_free_result($result);


$plik = sprintf("6084539$key", $count);
$OUT = sprintf($HEAD, $count) . "\n\n" . $dane;
file_put_contents("$KATALOG_MAP/$plik.mp", $OUT . "\n\n\n");
unset($dane);

$SPIS_MAP .= "img=$plik.img\n";
}

$PV="[Map]
FileName=geocaches
MapVersion=100
ProductCode=1
Levels=2
Level0=13
Level1=12
Zoom0=5
Zoom1=6

MapsourceName=geocaches from geokrety.org
MapSetName=geocaches from geokrety.org
CDSetName=geocaches from geokrety.org
[End-Map]

[Files]
$SPIS_MAP
[END-Files]";

file_put_contents($KATALOG_MAP . "/preview.txt", $PV);

?>
