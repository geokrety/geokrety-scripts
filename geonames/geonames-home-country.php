#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


/* ----------------------------------------------- kraj ------------------------------------------------------- */

include("$geokrety_www/get_country_from_coords.php");



//ok:
$result = mysqli_query($link, "SELECT `userid`, `lat`, `lon` FROM `gk-users` WHERE `lat` != '' and `lon` != '' and country =''");

while ($row = mysqli_fetch_array($result)) {
    list($userid, $lat, $lon) = $row;


    $country='';

    echo "$userid, $lat, $lon::  ";

    $country=get_country_from_coords($lat, $lon);
    echo "[ $country] ";

    if ($country == '' or $country=='xyz' or $country==null) {
        $handle = fopen("http://ws.geonames.org/countryCode?lat=$lat&lng=$lon&radius=30", "rb");
        $country = strtolower(trim(fread($handle, 3)));
        echo "geonames...";
        if (($country == '<!d') or ($country == '')) {
            $country = 'xyz';
            echo "bach!\n";
        }
        fclose($handle);
        sleep(2);
    }


    echo "$country\n";

    $sql = "UPDATE `gk-users` SET  `country`='$country' WHERE `userid` = '$userid' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
}


mysqli_close($link);
