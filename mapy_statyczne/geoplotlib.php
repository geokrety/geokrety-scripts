#!/usr/bin/env php

<?php
// export danych do mapki statycznej

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$OUTPUT = "lon,lat\n";

$link = DBPConnect();

// ----------------------------- KRETY ------------------------------//

$sql = "SELECT lat, lon, alt
FROM `gk-ostatnieruchy` AS a
WHERE (logtype ='0' OR logtype = '3')
LIMIT 20000;";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    $OUTPUT .= $row['lon'] . "," . $row['lat']  . "\n";
}

mysqli_free_result($result);

// ----------------------------- OUT ------------------------------//

file_put_contents("$geokrety_www/rzeczy/geodata/world.txt", $OUTPUT);

?>
