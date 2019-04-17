#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";
include("$geokrety_www/get_country_from_coords.php");
include("geonames_api.php");


// --- kraj - country - get user without country but with home coordinates
$userWithoutCountrySql = "SELECT `userid`, `lat`, `lon` FROM `gk-users` WHERE `lat` != '' and `lon` != '' and (country ='' or country is null)";
$nbUserUpdated=0;

$link = GKDB::getLink();
$result = mysqli_query($link,$userWithoutCountrySql);
if ($result === FALSE) {
  echo 'invalid query:'.mysqli_error($link).'\n';
  die;
}

while ($row = mysqli_fetch_array($result)) {
    list($userid, $lat, $lon) = $row;
    $country='';
    echo "$userid, $lat, $lon::  ";
    // first try from geo.kumy.org
    $country=get_country_from_coords($lat, $lon);
    echo "[ $country] ";

    if ($country == '' or $country=='xyz' or $country==null) {
        // in case of failure, ask to geonames
        $country = geonames_country_from_coords($geonamesEndpoint, $geonamesUsername, $lat, $lon);
        if (($country == '<!d') or ($country == '')) {
            $country = 'xyz';
            echo "bach!\n";
        }
        sleep(2);
    }
    echo "$country\n";

    // update user with country
    $sql = "UPDATE `gk-users` SET  `country`='$country' WHERE `userid` = '$userid' LIMIT 1";
    $result2 = mysqli_query($link, $sql);
    $nbUserUpdated++;
}
mysqli_close($link);

echo "nbUserUpdated: $nbUserUpdated\n";