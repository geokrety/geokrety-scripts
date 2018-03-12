#!/usr/bin/env php

<?php

$t1=microtime(true);

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT ru.`waypoint`, wp.`name`, wp.link, wp.owner, wp.country, count(distinct(ru.`id`)) as ile FROM `gk-ruchy` ru
left join `gk-waypointy` wp on ru.waypoint=wp.waypoint
WHERE ru.waypoint != '' and ru.logtype not in ('2', '3') group by ru.waypoint order by ile desc limit 50";

$result = mysqli_query($link, $sql);

$TRESC = "<table><tr><th>wpt</th><th>count</th><th>name</th><th>owner</th></tr>";

while ($row = mysqli_fetch_row($result)) {
    list($wpt, $name, $wpt_link, $owner, $country, $count) = $row;
    $TRESC .= "<tr><td><img src='".CONFIG_CDN_COUNTRY_FLAGS."/$country.png' /> $wpt</td><td>$count</td><td><a href='$wpt_link'>$name</a></td><td>$owner</td></tr>\n";
}
$TRESC .= "</table>";

$t2 = microtime(true);

$TRESC .= "<p>Generated: " . date("r") . "<br />" . ($t2 - $t1) . "s.</p>";

file_put_contents("$geokrety_www/files/hubs.html", $TRESC);

mysqli_close($link);
?>
