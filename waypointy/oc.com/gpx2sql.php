#!/usr/bin/env php
<?php
//śćńółźć

include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


$BAZY[] = "oc2.com.gpx";

foreach ($BAZY as $baza) {
    $xml_raw = file_get_contents($baza);

    //$xml_raw = file_get_contents("http://www.geopeitus.ee/?p=306");
    //$xml_raw = file_get_contents("estonia.gpx");
    //$xml_raw = file_get_contents($baza['url']);

    $xml = simplexml_load_string($xml_raw);

    //print_r($xml); die();

    foreach ($xml->wpt as $cache) {

/*
       <groundspeak:cache archived="False" available="True" id="2391294">
           <groundspeak:name>Blutsbrüder</groundspeak:name>
           <groundspeak:placed_by>Winny1200</groundspeak:placed_by>

        <ox:opencaching>
                    <ox:ratings>
                                    <ox:awesomeness>3.5</ox:awesomeness>
*/


        $name = trim(mysqli_escape_string($link, strtr((string) $cache->urlname, array('"' => ''))));
        $owner = mysqli_escape_string($link, (string) $cache->{'groundspeakcache'}->{'groundspeakplaced_by'});
        $waypoint = (string) $cache->name;
        $lat = (string) $cache['lat'];
        $lon = (string) $cache['lon'];
        $typ = (string) $cache->type;
        $linka = (string) $cache->url;
        $country = (string) $cache->country;

        $status = (string) $cache->groundspeakcache['available'];
        if ($status=='True') {
            $status=1;
        } else {
            $status=2;
        }

        echo "N $lat, E$lon, name: $name, by: $owner\nwpt: $waypoint, T: $typ, L: $linka, C: $country, Stat: $status\n\n";

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `owner`,  `typ`, `kraj` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$owner', '$typ', '$kraj', '$linka', '1')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `owner`='$owner', `typ`='$typ', `kraj`='$kraj', `link`='$linka', `status`='$status'";

        $result = mysqli_query($sql) or die("error 1: $id $sql");
    }
}

file_put_contents("data-xml.txt", "opencaching.com;" . date("Y-m-d H:i:s"));

mysqli_close($link);
?>
