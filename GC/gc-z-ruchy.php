#!/usr/bin/env php

<?php

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$data = date("r");

$sql ="SELECT  `waypoint`, `lat`, `lon`, `country`, `alt`  FROM `gk-ruchy` WHERE `waypoint` like 'GC%' group by `waypoint`";
$result = mysqli_query($link, $sql);


while ($row = mysqli_fetch_row($result)) {
    list($wpt, $lat, $lon, $country, $alt) = $row;

    echo "$wpt\n";

    $sql_ins="INSERT INTO `gk-waypointy-gc` (`wpt`, `lat`, `lon`, `country`, `alt`) VALUES ('$wpt', '$lat', '$lon', '$country', '$alt');";

    //echo $sql_ins; die;
    $result2 = mysqli_query($link, $sql_ins);
}
