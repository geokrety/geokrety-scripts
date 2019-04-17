#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
include("$geokrety_www/get_country_from_coords.php");


$link = GKDB::getLink();

/* ----------------------------------------------- kraj ------------------------------------------------------- */

//ok:
//$result = mysqli_query("SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (`lat`!='0' AND `lon`!='0') AND (`country` IS NULL or `country`='' or `country`='?')");
$result = mysqli_query($link, "SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (`lat`!='0' AND `lon`!='0') AND (`country` IS NULL or `country`='' or `country`='xyz') AND `ruch_id`>800");

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $lat, $lon) = $row;


    $country='';

    echo "$ruch_id, $lat, $lon::  ";

    $url="http://maps.google.com/maps/geo?q=$lat,$lon&output=json&sensor=false";
    $data = @file_get_contents($url);
    $jsondata = json_decode($data, true);
    if (is_array($jsondata)&& $jsondata ['Status']['code']==200) {
        $country = $jsondata ['Placemark'][0]['AddressDetails']['Country']['CountryNameCode'];
    }




    if ($country != '') {
        $sql = "UPDATE `gk-ruchy` SET  `country`='$country' WHERE `ruch_id` = '$ruch_id' LIMIT 1";
        $result2 = mysqli_query($link, $sql);
        echo "$country\n";
    }
    echo "dzzyt!\n";
}


mysqli_close($link);
