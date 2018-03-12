#!/usr/bin/env php

<?php
include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

// ---------------------- zaczynamy rajdy

// rajdy do rozpoczęscia

$result = mysqli_query($link, "SELECT `raceid` FROM `gk-races` WHERE (`racestart` <= NOW() AND `status` = '0')");

while ($row = mysqli_fetch_row($result)) {
    $raceid=$row[0];
    echo "Rozpoczyna się rajd: $raceid\n";

    // geokrety z rajdów do rozpoczęcia
    $result2 = mysqli_query($link, "SELECT geokretid FROM `gk-races-krety` WHERE `raceid` = '$raceid'");

    while ($row2 = mysqli_fetch_row($result2)) {
        $geokretid=$row2[0];

        echo "gkid: $geokretid\n";

        $result3 = mysqli_query($link, "SELECT `droga`, `skrzynki` FROM `gk-geokrety` WHERE `id` = '$geokretid'");
        list($droga, $skrzynki) = mysqli_fetch_row($result3);

        echo "Droga i skrzynki kreta: $droga, $skrzynki\n";

        // dla kazdego kreta z wyscigu: zresetowanie odległości
        $sql="UPDATE `gk-races-krety` SET `initDist` = '$droga',`initCaches` = '$skrzynki', `distToDest` = NULL , `finished` = NULL
WHERE `raceid` = '$raceid' and `geokretid` = '$geokretid' LIMIT 1";
        mysqli_query($link, $sql);
    } //każdy geokretid
} // rajdy do rozpoczęcia



// ---------------------- rozpoczęcie rajdów, które powinny się już zacząć //
$result = mysqli_query($link, "UPDATE `gk-races` SET `status` = '1' WHERE (`racestart` <= NOW() AND `status` = '0')");




echo "\n\n ------------- finished ---------\n\n";


// ----------------------  skończone na skończone :)


$result = mysqli_query($link, "SELECT `raceid` FROM `gk-races` WHERE (NOW() > DATE_ADD(`raceend`, INTERVAL 2 HOUR)  AND `status` = '1')");

while ($row = mysqli_fetch_row($result)) {
    $raceid=$row[0];
    echo "Zakończył się rajd: $raceid\n";

    // geokrety z rajdów do zakończenia
    $result2 = mysqli_query($link, "SELECT geokretid FROM `gk-races-krety` WHERE `raceid` = '$raceid'");

    while ($row2 = mysqli_fetch_row($result2)) {
        $geokretid=$row2[0];

        echo "gkid: $geokretid\n";

        $sql="SELECT gk.`droga` , gk.`skrzynki` , r.`lat` , r.`lon`
FROM `gk-geokrety` gk
LEFT JOIN `gk-ruchy` r ON gk.`ost_pozycja_id` = r.`ruch_id`
WHERE gk.`id` = '$geokretid'";

        //echo $sql; die();

        $result3 = mysqli_query($link, $sql);
        list($droga, $skrzynki, $lat, $lon) = mysqli_fetch_row($result3);

        if ($lat == '') {
            $lat = 'NULL';
        }
        if ($lon == '') {
            $lon = 'NULL';
        }

        echo "Droga i skrzynki kreta: $droga, $skrzynki, $lat, $lon\n";

        // dla kazdego kreta z wyscigu:
        $sql="UPDATE `gk-races-krety` SET `finishDist` = '$droga',`finishCaches` = '$skrzynki', `finishLat` = $lat, `finishLon` = $lon
WHERE `raceid` = '$raceid' and `geokretid` = '$geokretid' LIMIT 1";
        mysqli_query($link, $sql);
    } //każdy geokretid
} // rajdy do rozpoczęcia

$result = mysqli_query($link, "UPDATE `gk-races` SET `status` = '2' WHERE (NOW() > DATE_ADD(`raceend`, INTERVAL 2 HOUR)  AND `status` = '1')");


mysqli_close($link);

?>
