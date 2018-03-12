#!/usr/bin/env php
<?php
//śćńółźć

include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


$BAZY[] = "http://wpg.alleycat.pl/allwps.php";
//$BAZY[] = "geocaching_plus_ro.gpx";

foreach ($BAZY as $baza) {
    $xml_raw = strtr(file_get_contents($baza), array('&' => '+'));

    $xml = simplexml_load_string($xml_raw, "SimpleXMLElement", LIBXML_NOENT | LIBXML_NOCDATA);

    //print_r($xml); die();

    /*
    <wpt lat="52.181465" lon="20.744280">
    <name>WP#289 Folwark Moszna - Spichlerz</name>
    <desc>aktywny, warto��: 1</desc>
    <url>http://wpg.alleycat.pl/wp289</url>
    */


    foreach ($xml->wpt as $cache) {
        $name = trim(mysqli_escape_string($link, strtr((string) $cache->name, array('"' => ''))));
        $linka = (string) $cache->url;
        $lat = (string) $cache['lat'];
        $lon = (string) $cache['lon'];
        $waypoint = "WPG-" . trim(substr($name, 0, 7));

        //echo "N $lat, E$lon, name: $name, WPT: $wpt L: $linka\n\n";

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name`, `link`, `status` )
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$linka', '1')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `link`='$linka', `status`='1'";

        echo "$sql\n\n";
        $result = mysqli_query($link, $sql) or die("error 1: $id $sql");
    }
}

file_put_contents("data-xml.txt", "WaypointGame;" . date("Y-m-d H:i:s"));
mysqli_close($link);
?>
