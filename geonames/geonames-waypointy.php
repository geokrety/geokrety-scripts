#!/usr/bin/env php
<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
include("$geokrety_www/get_country_from_coords.php");
include("geonames_api.php");


$link = GKDB::getLink();
$nbUpdated=0;

// --- kraj - country - get waypointy without country but with coordinates

//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` IS NULL) AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%' AND `waypoint` NOT LIKE 'TR%' AND `waypoint` NOT LIKE 'VI%' AND `waypoint` NOT LIKE 'MS%'");
//$result = mysqli_query("SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` = 'xyz') AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%'");

// sprawdza tylko nowe - tak ma być
$result = mysqli_query($link, "SELECT waypoint, lat, lon FROM `gk-waypointy` WHERE (`country` IS NULL) AND (lat != 0 AND lon != 0) AND `waypoint` NOT LIKE 'GD%'");

echo "start!\n";

while ($row = mysqli_fetch_array($result)) {
    list($waypoint, $lat, $lon) = $row;
    $country='';

    echo "$waypoint, $lat, $lon::  ";

    // get from geo.kumy.org
    $country=get_country_from_coords($lat, $lon);
    //print_r($country);
    echo "[ $country] ";


    if ($country == '' or $country == 'xyz') {
        $country = geonames_country_from_coords($geonamesEndpoint, $geonamesUsername, $lat, $lon);
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
    $nbUpdated++;
    $sql = "UPDATE `gk-waypointy`  SET  `country`='$country' WHERE `waypoint` = '$waypoint' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
}

mysqli_close($link);
echo "nbUpdated: $nbUpdated\n";