#!/usr/bin/env php
<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


/* ----------------------------------------------- wysokos ------------------------------------------------------- */
/*

$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE alt<'-3000' AND `waypoint` NOT LIKE 'GD%'");

//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE `link` LIKE 'http://www.geopeitus.ee%'");

// powtórne sprawdzenie
//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE alt='-3000'" AND `waypoint` NOT LIKE 'GD%');



while ($row = mysqli_fetch_array($result)) {
list($waypoint, $lat, $lon) = $row;

echo "$waypoint, $lat, $lon::  ";

$handle = fopen("http://ws.geonames.org/srtm3?lat=$lat&lng=$lon", "rb");
 $alt = trim(fread($handle, 5));
  fclose($handle);
   if(!is_numeric($alt) OR $alt < -3000) {
       echo "SRTM failed. Trying GTOPO30... ";
       $handle = fopen("http://ws.geonames.org/gtopo30?lat=$lat&lng=$lon&radius=15", "rb");
       $alt = trim(fread($handle, 5));
       fclose($handle);
    }
 if(!is_numeric($alt) OR $alt < -3000) $alt = '-2000';

echo "$alt\n";

$sql = "UPDATE `gk-waypointy` SET `alt` = '$alt' WHERE `waypoint` = '$waypoint' LIMIT 1";
$result2 = mysqli_query ($sql);
sleep(1);

}

*/

/* ----------------------------------------------- kraj ------------------------------------------------------- */

include("$geokrety_www/get_country_from_coords.php");

//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` IS NULL) AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%' AND `waypoint` NOT LIKE 'TR%' AND `waypoint` NOT LIKE 'VI%' AND `waypoint` NOT LIKE 'MS%'");
//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` = 'xyz') AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%'");


// sprawdza tylko nowe - tak ma być
$result = mysqli_query($link, "SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` IS NULL) AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%'");


echo "start!\n";

while ($row = mysqli_fetch_array($result)) {
    list($waypoint, $lat, $lon) = $row;
    $country='';

    echo "$waypoint, $lat, $lon::  ";

    $country=get_country_from_coords($lat, $lon);
    //print_r($country);
    echo "[ $country] ";


    if ($country == '' or $country == 'xyz') {
        $handle = fopen("http://ws.geonames.org/countryCode?lat=$lat&lng=$lon&radius=30", "rb");
        $country = strtolower(trim(fread($handle, 3)));
        echo "geonames...";
        fclose($handle);
        sleep(2);
    }


    if (($country == '<!d') or ($country == '')) {
        $url="http://maps.google.com/maps/geo?q=$lat,$lon&output=json&sensor=false";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);
        if (is_array($jsondata)&& $jsondata ['Status']['code']==200) {
            $country = $jsondata ['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'];
        }
    }

    if (($country == '<!d') or ($country == '')) {
        $country = 'xyz';
        echo "google bach!\n";
    }

    $country = strtolower($country);

    echo "$country\n ";
    $sql = "UPDATE `gk-waypointy`  SET  `country`='$country' WHERE `waypoint` = '$waypoint' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
}


mysqli_close($link);
