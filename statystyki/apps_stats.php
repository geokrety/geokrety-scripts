#!/usr/bin/env php

<?php

$t1=microtime(true);

include_once("../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");

$link = DBPConnect();

$TRESC = date("Y-m-d");
$TRESC .= "\n\n";

$sql = "SELECT app, count(ruch_id) FROM `gk-ruchy` group by `app`";
$result = mysqli_query($link, $sql);
$TRESC .= "app	count\n-----------------------------------\n";
while ($row = mysqli_fetch_row($result)) {
    list($app, $app_ver, $count) = $row;
    $TRESC .= "$app	$app_ver	$count\n";
}



$sql = "SELECT app, app_ver, count(ruch_id) FROM `gk-ruchy` group by `app`, `app_ver`";
$result = mysqli_query($link, $sql);
$TRESC .= "\n\napp	app_ver	count\n-----------------------------------\n";
while ($row = mysqli_fetch_row($result)) {
    list($app, $app_ver, $count) = $row;
    $TRESC .= "$app	$app_ver	$count\n";
}

$t2 = microtime(true);


print $TRESC;
file_put_contents("$geokrety_www/files/stat_apps.txt", $TRESC);

?>
