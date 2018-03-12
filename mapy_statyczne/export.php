#!/usr/bin/env php

<?php
// export danych do mapki statycznej

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

// ----------------------------- KRETY ------------------------------//

$sql = "SELECT lat, lon, alt
FROM `gk-ostatnieruchy` AS a
WHERE (logtype ='0' OR logtype = '3') and alt >= '0'
LIMIT 20000;";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_array($result)) {
    $OUTPUT .= $row['lon'] . " " . $row['lat']  . " " . $row['alt'] . "\n";
}

mysqli_free_result($result);

// ----------------------------- OUT ------------------------------//


file_put_contents("swiat/geokrety.txt", $OUTPUT);

?>
