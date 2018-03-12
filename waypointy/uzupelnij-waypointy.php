#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


/* ----------------------------------------------- ruchy bez wpt ------------------------------------------------------- */


$result = mysqli_query($link, "SELECT `ruch_id`, `lat`, `lon` FROM `gk-ruchy` WHERE `lat` != '' and `lon` != '' and `waypoint` = ''");

while ($row = mysqli_fetch_array($result)) {
    list($ruch_id, $lat, $lon) = $row;


    echo "$ruch_id, $lat, $lon::  ";

    $result2 = mysqli_query($link, "SELECT `waypoint` FROM `gk-ruchy` WHERE `lat`='$lat' and `lon` = '$lat' and `waypoint` != '' LIMIT 1");
    $row2 = mysqli_fetch_array($result2);
    if (!empty($row2)) {
        $count++;
        $wpt = $row2[0];
        echo "	$wpt	($count)\n";
        $result3 = mysqli_query($link, "UPDATE `gk-ruchy` SET `waypoint`='$wpt' WHERE `ruch_id` = '$ruch_id' LIMIT 1");
    } else {
        echo "	brak!\n";
    }
}

mysqli_close($link);

?>
