#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
include("$geokrety_www/get_country_from_coords.php");

// --- kraj - country - get ruchy without country but with coordinates

$link = GKDB::getLink();
$nbUpdated=0;
// ruchy.logtype   	0=drop, 1=grab, 2=comment, 3=met, 4=arch, 5=dip

// get ruchy that are not comment or grab and without country but with lat/lon
$selectRuchyWithoutCountry = <<<EOSQL
SELECT ruch_id, lat, lon FROM `gk-ruchy`
WHERE `logtype`!='2' AND `logtype`!='1'
  AND (`lat`!='0' AND `lon`!='0')
  AND (`country` IS NULL or `country`='' or `country`='xyz' or `country`='err')
EOSQL;

$startRuchyId = 730000;
if (isset($startRuchyId)) {
  $selectRuchyWithoutCountry .= " AND `ruch_id`>$startRuchyId";
}

$result = mysqli_query($link, $selectRuchyWithoutCountry);
if ($result === FALSE) {
  echo 'invalid query:'.mysqli_error($link).'\n';
  die;
}

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $lat, $lon) = $row;
    $country='';
    echo "$ruch_id, $lat, $lon::  ";

    // get from geo.kumy.org
    $country=get_country_from_coords($lat, $lon);

    echo "[ $country] ";

    echo " google...";
    if ($country == '' or $country=='xyz' or $country==null or $country=='?') {
        //$url="http://maps.google.com/maps/geo?q=$lat,$lon&output=json&sensor=false";
        $url="https://maps.googleapis.com/maps/api/geocode/json?latlng=$lat,$lon&sensor=false";
        $data = @file_get_contents($url);
        $jsondata = json_decode($data, true);
        //if(is_array($jsondata )&& $jsondata ['Status']['code']==200)  $country = $jsondata ['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'];
        echo "status: "  . $jsondata['status'] . "\t";

        if (is_array($jsondata) and $jsondata['status']=="OK") {
            $data = array();
            foreach ($jsondata['results']['0']['address_components'] as $element) {
                $data[ implode(' ', $element['types']) ] = $element['short_name'];
            }
            //print_r($data);

            $country = $data['country political'];
        }

        sleep(1);
    }

    $country = strtolower($country);
    if ($country == '') {
        $country ='xyz';
    }

    echo "$country\n";

    $nbUpdated++;
    $sql = "UPDATE `gk-ruchy` SET  `country`='$country' WHERE `ruch_id` = '$ruch_id' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
}
mysqli_close($link);

echo "nbUpdated: $nbUpdated\n";
?>
