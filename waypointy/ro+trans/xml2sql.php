#!/usr/bin/env php
<?php
//śćńółźć



$BAZY_OC['ro']['prefix'] = 'GR';
$BAZY_OC['ro']['url'] = "http://geotrekking.plus.ro/download_all.php";
//$BAZY_OC['ro']['url'] = "ro.gpx";
$BAZY_OC['ro']['szukaj'] = 'http://geotrekking.plus.ro/modules.php?name=News&file=article&sid=';


$BAZY_OC['trans']['prefix'] = 'RH';
$BAZY_OC['trans']['url'] = "http://www.rejtekhely.ro/download_all.php";
$BAZY_OC['trans']['szukaj'] = 'http://www.rejtekhely.ro/modules.php?name=News&file=article&sid=';



include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

foreach ($BAZY_OC as $baza) {
    echo $baza['prefix'] . "\n\n";
    if ($baza['prefix'] != 'OZ') {
        $url = $baza['url']. $modifiedsince;
    } else {
        $url = $baza['url'];
    }
    $xml_raw = file_get_contents($url);
    $xml = @simplexml_load_string($xml_raw);

    if (!$xml) {
        echo "Invalid xml file at: $url\n";
        continue;
    }

    //print_r($xml); die();

    foreach ($xml->wpt as $cache) {
        $name = trim(mysqli_escape_string($link, strtr((string) $cache->desc, array('"' => ''))));
        $owner = mysqli_escape_string($link, (string) $cache->userid);
        $waypoint = (string) $cache->name;
        $lon = (string) $cache['lon'];
        $lat = (string) $cache['lat'];
        $typ = (string) $cache->type;
        $kraj = (string) $cache->country;
        $status = (int) $cache->status['id'];
        $linka = (string) $cache->url;

        //$link_sufix=substr($waypoint,2,4);
        //$linka = $baza['szukaj'] . $link_sufix;

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `owner`,  `typ`, `kraj` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$owner', '$typ', '$kraj', '$linka', '$status')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `owner`='$owner', `typ`='$typ', `kraj`='$kraj', `link`='$linka', `status`='$status'";

        $result = mysqli_query($link, $sql) or die("error 1: $id $sql");

        echo "$name $owner $waypoint $lon $lat $typ $kraj\n";
    }
}


file_put_contents("data-xml.txt", "geotrekking.plus.ro www.rejtekhely.ro;" . date("Y-m-d H:i:s"));
mysqli_close($link);
?>
