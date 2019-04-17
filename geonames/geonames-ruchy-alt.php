#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = GKDB::getLink();

// -- WYSOKO�� - altitude

$link = GKDB::getLink();

// get ruchy trip step without good altitude
$result = mysqli_query($link, "SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (alt<'-2000')");
// sprawdzenie powt�rne
//$result = mysqli_query($link, "SELECT ruch_id, lat, lon FROM `gk-ruchy` WHERE `logtype`!='2' AND `logtype`!='1' AND (alt='-2000')");

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $lat, $lon) = $row;

    echo "$ruch_id, $lat, $lon:: SRTM... ";
    if (is_null($lat) || is_null($lon)) {
      echo "skip\n ";
      continue;
    }

    $handle = fopen("https://geo.kumy.org/api/getElevation?lat=$lat&lon=$lon", "rb");
    $alt = trim(fread($handle, 5));

    fclose($handle);
    if (!is_numeric($alt) or $alt < -3000) {
        echo "SRTM failed.";
    }
    if (!is_numeric($alt) or $alt < -3000) {
        $alt = '-2000';
    }

    echo "$alt\n ";


    $sql = "UPDATE `gk-ruchy` SET `alt` = '$alt' WHERE `ruch_id` = '$ruch_id' LIMIT 1";
    //echo "$sql\n";
    $result2 = mysqli_query($link, $sql);

    sleep(0.2);
}


mysqli_close($link);
