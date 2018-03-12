#!/usr/bin/env php

<?php

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT date(data) as dzien, (YEAR(data) + DAYOFYEAR(date(data))/366) as data, count( id )
FROM `gk-geokrety`
GROUP BY dzien;";

$result = mysqli_query($link, $sql);

$OUT = "data;data_doy;gk\n";

while ($row = mysqli_fetch_row($result)) {
    list($dzien, $ts, $ile) = $row;
    $ile_total=$ile_total+$ile;
    $OUT .= "$dzien;$ts;$ile_total\n";
}


file_put_contents("out/gk-dzienpodniu.csv", $OUT);

?>
