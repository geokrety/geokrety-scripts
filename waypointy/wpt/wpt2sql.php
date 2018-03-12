#!/usr/bin/env php
<?php
//śćńółźć

$BAZY['au']['plik'] = 'gca.wpt';
$BAZY['au']['szukaj'] = 'http://geocaching.com.au/cache/';

include_once("../../konfig-tools.php");
include_once("$geokrety_www/templates/konfig.php");
require_once "$geokrety_www/__sentry.php";

$link = DBPConnect();

foreach ($BAZY as $baza) {
    $linie = file($baza['plik']);

    foreach ($linie as $linia) {
        //1,GA0915,-41.162367,146.331450,39384.00000,0,1,3,0,65535,Castaway,0,0,0,-777,6,0,17
        //2,GA0498,-35.241333,149.067500,38908.00000,0,1,3,0,65535,Lizzy,0,0,0,-777,6,0,17

        //2,VI98,24.68750000,35.08211667,37718.0000000,117,1,6,0,65535,Shams Alam о� Crab12,2,0,0,-777,6,0,17
        //3,VI233,58.04538333,6.97909997,37864.0000000,117,1,6,0,65535,Hausvikkoden о� Mikle_V,2,0,0,-777,6,0,17

        $dane = explode(",", $linia);
        if ($dane[1] != '') {
            $waypoint = $dane[1];
            $lat = $dane[2];
            $lon = $dane[3];

            if ($baza['prefix'] == 'ru') {
                $linka_wpt = substr($waypoint, 2, 10);
                $linka = $baza['szukaj'] . $linka_wpt;
            } elseif ($baza['prefix'] == 'gpsgames') {
                $linka_wpt = substr($waypoint, 2, 10);
                $linka = $baza['szukaj'] . dechex($linka_wpt);
            } else {
                $linka = $baza['szukaj'] . $waypoint;
            }

            $name = mysqli_escape_string($link, $dane[10]);



            $sql = "INSERT INTO `gk-waypointy` ( `waypoint`, `lat` , `lon` , `name` , `link`, `status`)
VALUES ('$waypoint',  '$lat', '$lon', '$name', '$linka', '1')
ON DUPLICATE KEY UPDATE `waypoint`='$waypoint', `lat`='$lat', `lon`='$lon', `name`='$name', `link`='$linka', `status`='1'";

            //echo $sql; die();
            $result = mysqli_query($link, $sql) or die("error 1: $id $sql");

            //echo "$name $waypoint $lon $lat\n$linka\n\n";
        }
    }
}

file_put_contents("data-xml.txt", "GPSGames, gca.com;" . date("Y-m-d H:i:s"));
mysqli_close($link);
?>
