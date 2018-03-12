#!/usr/bin/env php
<?php
//śćńółźć



$BAZY_OC['cz']['prefix'] = 'OZ';
$BAZY_OC['cz']['url'] = "occz.gpx";
$BAZY_OC['cz']['szukaj'] = 'http://www.opencaching.de/searchplugin.php?userinput=';



include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

foreach ($BAZY_OC as $baza) {
    echo $baza['prefix'] . "\n\n";

    $xml_raw = file_get_contents($baza['url']);

    $xml = simplexml_load_string($xml_raw);

    //print_r($xml); die();

    foreach ($xml->wpt as $wpt) {
        $name = trim(mysqli_escape_string($link, strtr((string) $wpt->desc, array('"' => ''))));
        $owner = $wpt->geocache->owner;
        $waypoint = (string) $wpt->name;
        $lon = (string) $wpt['lon'];
        $lat = (string) $wpt['lat'];
        $typ = (string) $wpt->geocache->type;
        $kraj = (string) $wpt->geocache->country;
        $linka = (string) $wpt->url;

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `owner`,  `typ`, `kraj` , `link`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$owner', '$typ', '$kraj', '$linka')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `owner`='$owner', `typ`='$typ', `kraj`='$kraj', `link`='$linka'";

        $result = mysqli_query($link, $sql) or die("error 1: $id $sql");

        echo "$name (owner: $owner) $waypoint $lon $lat $typ $kraj\n";
    }
}
file_put_contents("data-xml.txt", "oc.cz;" . date("Y-m-d H:i:s"));
mysqli_close($link);
?>
