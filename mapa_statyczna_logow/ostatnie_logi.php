#!/usr/bin/env php

<?php
// export danych do mapki statycznej

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

// ----------------------------- KRETY ------------------------------//

$sql = "SELECT `lat`,`lon` FROM `gk-ruchy` WHERE `lat` IS NOT NULL and `lon` IS NOT NULL order by `ruch_id` DESC LIMIT 100;";

$result = mysqli_query($link, $sql);
$OUTPUT = '';

while ($row = mysqli_fetch_array($result)) {
    $OUTPUT .= sprintf("%.2f", $row['lat']) . "," . sprintf("%.2f", $row['lon']) . "|";
}

mysqli_free_result($result);

// ----------------------------- OUT ------------------------------//

file_put_contents("./lastlogs.txt", $OUTPUT);
