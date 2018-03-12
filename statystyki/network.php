#!/usr/bin/env php

<?php

$t1=microtime(true);

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT waypoint, id, droga FROM `gk-ruchy` WHERE waypoint != '' and `logtype`in ('0', '3', '5')
order by `id`, `data`";

$result = mysqli_query($link, $sql);


while ($row = mysqli_fetch_row($result)) {
    list($wpt, $id, $droga) = $row;
    if ($id != $idOLD) { // nowy geokrety
        $idOLD=$id;
        $wptOLD='';
    }

    if ($wptOLD != '') {
        echo "$wptOLD;$wpt;$droga;$id\n";
    }

    $wptOLD=$wpt;
}

$t2 = microtime(true);

file_put_contents("$geokrety_www/files/hubs.html", $TRESC);

?>
