#!/usr/bin/env php

<?php
// stat by current country

$t1=microtime(true);

// list of GK in archived/destroyed caches

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT country, count(distinct id) AS ile_kretow
FROM `gk-ostatnieruchy`
WHERE `logtype` = ('O' OR '3')
GROUP BY country
ORDER BY ile_kretow DESC";

$result = mysqli_query($link, $sql);

$TRESC = "<table><tr><th>country</th><th>geokrets</th></tr>";
$TRESC_GOOGLE = '';

while ($row = mysqli_fetch_row($result)) {
    list($country, $kretow) = $row;
    if ($country=='xyz') {
        $country = 'unknown';
        $linka ="";
    } else {
        $linka = "http://en.wikipedia.org/wiki/." . $country;
    }
    $TRESC .= '<tr><td><a href="' . $linka . '"><img src="'.CONFIG_CDN_COUNTRY_FLAGS.'/'.$country.'.png" alt="'.$country.'" title="'.$country.'" width="16" height="11" border="0" /></a> ' . $country .'
<a href="/szukaj.php?country=' . $country . '">search</a></td>
<td>'.$kretow.'</td>
</tr>' . "\n";

    $TRESC_GOOGLE .= "['$country', $kretow],\n";
}
$TRESC .= "</table>";



$t2 = microtime(true);

$TRESC .= "<p>Generated: " . date("r") . "<br />" . ($t2 - $t1) . "s.</p>";

file_put_contents("$geokrety_www/files/country_stat.html", $TRESC);
file_put_contents("$geokrety_www/files/country_stat_google.html", $TRESC_GOOGLE);
?>
