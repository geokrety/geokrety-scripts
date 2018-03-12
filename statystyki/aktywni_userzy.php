#!/usr/bin/env php
<?php

$aktywni=3;
$baktywni=10;

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

// -------------------------------------- nowe rzeczy co...  ------------------------------- //


$result = mysqli_query($link, "SELECT COUNT( `id` ) FROM `gk-geokrety` ");
list($stat_geokretow, $stat_droga) = mysqli_fetch_array($result);
mysqli_free_result($result);

for ($rok = 2007; $rok <= date("Y"); $rok++) {
    for ($mc = 1; $mc <= 12; $mc++) {
        echo "$rok-$mc	";

        $sql="select count(distinct t1.user) as ile_userow FROM
(SELECT count( id ) AS ile, user
FROM `gk-ruchy`
WHERE year( data ) = $rok and month(data) = $mc
AND user >0
GROUP BY user
HAVING ile > $aktywni) as t1";

        $result = mysqli_query($link, "$sql");
        list($ile_aktywni) = mysqli_fetch_array($result);
        mysqli_free_result($result);


        $sql="select count(distinct t1.user) as ile_userow FROM
(SELECT count( id ) AS ile, user
FROM `gk-ruchy`
WHERE year( data ) = $rok and month(data) = $mc
AND user >0
GROUP BY user
HAVING ile > $baktywni) as t1";

        $result = mysqli_query($link, "$sql");
        list($ile_baktywni) = mysqli_fetch_array($result);
        mysqli_free_result($result);



        echo "$ile_aktywni	$ile_baktywni\n";
    }
}
mysqli_close($link);
