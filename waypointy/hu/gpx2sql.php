#!/usr/bin/env php
<?php
//śćńółźć

include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();


//$BAZY[] = "http://www.geopeitus.ee/?p=306";
$BAZY[] = "http://www.geocaching.hu/caches.geo?egylapon=20&dist_lat=&dist_lon=&filetype=gpx_easygps&wp_logs_max=&waypoint_xml=%3Cfield+name%3D%22kod%22+deaccent%3D%22i%22%2F%3E&description_xml=%3Cfield+name%3D%22nev%22+deaccent%3D%22i%22+length%3D%2224%22%2F%3E%3A%3Cfield+name%3D%22nehezseg_0%22%2F%3E%2F%3Cfield+name%3D%22terep_0%22%2F%3E%2C%3Cfield+name%3D%22tipus%22%2F%3E%2CR%3Cfield+name%3D%22elrejtes%22+format%3D%22ymd%22%2F%3E%2CU%3Cfield+name%3D%22utolso_megtalalas%22+format%3D%22ymd%22%2F%3E&content_type=default&content_disposition=default&filename=&compression=default&wp_limit=&submit_waypoints=Let%F6lt%E9s&any=&nickname=&fulldesc=&placer=&waypoint=&member=&nincs_meg_nekik=&dateid_min=&dateid_max=&diff_min=&diff_max=&terr_min=&terr_max=&length_min=&length_max=&alt_min=&alt_max=&db_m_min=&db_m_max=&db_j_min=&db_j_max=&db_n_min=&db_n_max=&db_o_min=&db_o_max=&db_l_min=&db_l_max=&rating_place_min=&rating_place_max=&rating_cache_min=&rating_cache_max=&rating_web_min=&rating_web_max=&rating_min=&rating_max=&dateposted_min=&dateposted_max=&elsolog_min=&elsolog_max=&utolsolog_min=&utolsolog_max=&db_f_min=&db_f_max=&dist_min=&dist_max=&no_poi=i&id=magyarorszag&filter=i";

//$BAZY[] = "geocaching_plus_ro.gpx";

foreach ($BAZY as $baza) {
    $xml_raw = file_get_contents($baza);

    //$xml_raw = file_get_contents("http://www.geopeitus.ee/?p=306");
    //$xml_raw = file_get_contents("estonia.gpx");
    //$xml_raw = file_get_contents($baza['url']);

    $xml = simplexml_load_string($xml_raw);

    //print_r($xml); die();

    foreach ($xml->wpt as $cache) {
        $name = trim(mysqli_escape_string($link, strtr((string) $cache->desc, array('"' => ''))));
        $owner = mysqli_escape_string($link, (string) $cache->owner);
        $waypoint = (string) $cache->name;
        $lat = (string) $cache['lat'];
        $lon = (string) $cache['lon'];
        $typ = (string) $cache->type;
        $linka = (string) $cache->url;
        $country = (string) $cache->country;

        echo "N $lat, E$lon, name: $name, by: $owner\nwpt: $waypoint, T: $typ, L: $linka, C: $country\n\n";

        $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `owner`,  `typ`, `kraj` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$owner', '$typ', '$kraj', '$linka', '1')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `owner`='$owner', `typ`='$typ', `kraj`='$kraj', `link`='$linka', `status`='1'";

        $result = mysqli_query($link, $sql) or die("error 1: $id $sql");
    }
}

file_put_contents("data-xml.txt", "geocaching.hu;" . date("Y-m-d H:i:s"));
mysqli_close($link);
?>
