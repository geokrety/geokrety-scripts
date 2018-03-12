#!/usr/bin/env php

<?php
// stat by current country

$t1=microtime(true);

// list of GK in archived/destroyed caches

include_once("../konfig-tools.php"); include_once("$geokrety_www/templates/konfig.php");

$link = DBPConnect();

// słownik

$sql = "SELECT country
FROM `gk-ruchy`
WHERE country != 'xyz' AND country != ''
GROUP BY COUNTRY
";

$result = mysqli_query($link, $sql);
file_put_contents("country_codes_dict.csv", "");    // initialize

$i=0;
while ($row = mysqli_fetch_row($result)) {
    list($country) = $row;
    $countries_dict["$country"] = $i;
    file_put_contents("country_codes_dict.csv", "$i	$country\n", FILE_APPEND);
    $i++;
}


//print_r($countries_dict);
//die(1);


$TRESC = ""; // "country	lat	lon\n";

$sql = "SELECT country, lat, lon
FROM `gk-ruchy`
WHERE country != 'xyz' AND country != '' AND lat != '' AND lon != ''
";

$result = mysqli_query($link, $sql);

while ($row = mysqli_fetch_row($result)) {
    list($country, $lat, $lon) = $row;
    $TRESC .= $countries_dict[$country] . " 1:$lat 2:$lon\n";
}


$t2 = microtime(true);

file_put_contents("./country-lat-lon.libsvm", $TRESC);
?>
