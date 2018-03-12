#!/usr/bin/env php

<?php

$t1=microtime(true);

// list of GK in archived/destroyed caches

include_once("../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

$sql = "SELECT a.id, b.nazwa, b.owner, c.user, a.waypoint, d.link, a.lat, a.lon
FROM `gk-ostatnieruchy` AS a LEFT JOIN `gk-geokrety` AS b
ON a.id = b.id
LEFT JOIN `gk-users` AS c
ON b.owner = c.userid
LEFT JOIN `gk-waypointy` AS d
ON a.waypoint = d.waypoint
WHERE logtype IN ('0','3')
AND a.waypoint IN (SELECT waypoint FROM `gk-waypointy` WHERE status != 1) AND a.waypoint != ''
ORDER BY c.user";

$result = mysqli_query($link, $sql);

$TRESC = "<table><tr><th>user</th><th>geokret</th><th>cache</th></tr>";
$XML = '';

while ($row = mysqli_fetch_row($result)) {
    list($id, $kret, $userid, $user, $waypoint, $link, $lat, $lon) = $row;
    $TRESC .= '<tr><td><a href="mypage.php?userid=' . $userid . '">'.$user .'</a></td>
<td><a href="konkret.php?id=' . $id . '">'.$kret.'</a>' . '</td>
<td><a href="' . $link . '">'.$waypoint.'</a></td>
</tr>' . "\n";

    // xml do mapki
    $XML .= '<geokret id="'. $id .'" dist="" lat="'. $lat .'" lon="'.$lon .'" waypoint="'.$waypoint.'" owner_id="'.$userid .'" state="" type="" image=""><![CDATA['.$kret .']]></geokret>' . "\n";
}
$TRESC .= "</table>";

$t2 = microtime(true);

$TRESC .= "<p>Generated: " . date("r") . "<br />" . ($t2 - $t1) . "s.</p>";

file_put_contents("$geokrety_www/files/lost.html", $TRESC);


// ------------------- XML --------------------------------- //

$now = date("Y-m-d H:i:s");

$OUTPUT = "<?xml version=\"1.0\" encoding=\"UTF-8\" standalone=\"yes\" ?>
<gkxml version=\"1.0\" date=\"$now\">
<geokrety>$XML</geokrety>
</gkxml>";

file_put_contents("$geokrety_www/files/lost.xml", $OUTPUT);

?>
