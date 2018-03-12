#!/usr/bin/env php
<?php

$loop_time=20;    // co sekund ile powtarzać

include_once("konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

do {
    $procesy = [];
    $result = mysqli_query($link, "SHOW FULL PROCESSLIST");
    while ($row=mysqli_fetch_array($result)) {
        if ($row['Info'] != '' and $row['Info'] != 'SHOW FULL PROCESSLIST') {
            $procesy[$row['Id']]=$row['Info'];
        }
    }


    sort($procesy);

    $poprz_query="";

    foreach ($procesy as $id => $query) {
        if ($query==$poprz_query) {
            echo "duplikat zapytania: $query\n";
            echo "	kill $id\n";
            $sql="KILL $id";
            mysqli_query($link, $sql);
        }
        $poprz_query=$query;
    }

    sleep($loop_time);
} while (1) {; }

?>
